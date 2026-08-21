#!/usr/bin/env python3
"""Compare static HTML pages (D:/Sites/website) against Blade views (Gocare-laravel).

For each static HTML page, apply the same mechanical transforms the blade views
already use (strip .html suffix from links) and report how much differs.
Also categorizes special pages that use Laravel templating (home, blog, gallery).
"""
import os
import re
import sys
import difflib

STATIC = r"D:\Sites\website"
VIEWS = r"C:\wamp64\www\Gocare-laravel\resources\views\pages"

# Map static page -> blade view (relative to resources/views/pages).
# Key: static relative path without .html; Value: blade relative path without .blade.php
# Hierarchical pages route to resources/views/pages/<slug>.blade.php so we only need
# this map for pages whose blade view lives in a different spot.

def find_blade(slug):
    """Given a static slug (relative path w/o extension), guess the blade view path."""
    return slug

def strip_html_links(text):
    """Replicate the mechanical transform used by the existing blade views:
    href/src/action attribute values like 'foo.html' -> 'foo', 'index.html' -> '/'.
    We ONLY rewrite the currently-known patterns; untouched strings stay as-is so
    they surface as diffs."""
    def repl(m):
        pre = m.group(1)
        val = m.group(2)
        rest = m.group(3)
        # foo.html  -> foo
        # foo.html#x -> foo#x (already handled since # part is after)
        if re.fullmatch(r'[A-Za-z0-9_\-./]+\.html', val):
            new = val[:-5]
            return f'{pre}{new}{rest}'
        return m.group(0)
    # href="....", src="....", action="....", data-src=...
    pat = re.compile(r'(?P<pre>(?:href|src|action|data-src)=")(?P<val>[^"]*)(?P<rest>")')
    return pat.sub(repl, text)

def main():
    static_files = []
    for root, dirs, files in os.walk(STATIC):
        for f in files:
            if f.endswith('.html'):
                full = os.path.join(root, f)
                rel = os.path.relpath(full, STATIC)
                static_files.append(rel)

    report = []
    for rel in sorted(static_files):
        slug = rel[:-5]  # strip .html
        # find candidate blade views
        candidates = []
        # primary guess: pages/<slug>.blade.php
        base = os.path.join(VIEWS, slug + '.blade.php')
        if os.path.exists(base):
            candidates.append((slug + '.blade.php', base))
        # also allow pages/<basename>.blade.php for subfolders if a flat file exists
        bname = os.path.basename(slug) + '.blade.php'
        flat = os.path.join(VIEWS, bname)
        if os.path.exists(flat) and not os.path.exists(base):
            candidates.append((bname, flat))

        with open(os.path.join(STATIC, rel), encoding='utf-8', errors='replace') as fh:
            static_text = fh.read()
        static_norm = strip_html_links(static_text)

        for cname, cpath in candidates:
            with open(cpath, encoding='utf-8', errors='replace') as fh:
                blade_text = fh.read()
            # Normalize: remove @include lines and blade directives to compare only visual html
            def drop_blade(m):
                return ''
            blade_min = re.sub(r'[ \t]*@[A-Za-z].*', '', blade_text)
            sm = difflib.SequenceMatcher(None, static_norm.splitlines(), blade_min.splitlines(), autojunk=False)
            ratio = sm.ratio()
            opcodes = [op for op in sm.get_opcodes() if op[0] != 'equal']
            nop = len(opcodes)
            report.append((rel, cname, ratio, nop))
            break

    report.sort(key=lambda x: x[2])
    print(f"{'STATIC PAGE':70} {'BLADE':40} {'RATIO':>7} {'OPC':>5}")
    for rel, cname, ratio, nop in report:
        print(f"{rel:70} {cname:40} {ratio:7.3f} {nop:5}")

if __name__ == '__main__':
    main()