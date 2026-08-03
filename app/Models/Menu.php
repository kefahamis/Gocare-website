<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    public const LOCATION_PRIMARY = 'primary';

    public const LOCATION_MOBILE = 'mobile';

    public const LOCATION_FOOTER = 'footer';

    protected $fillable = ['name', 'location', 'description', 'is_active', 'items'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'items' => 'array'];
    }

    public static function forLocation(string $location): ?self
    {
        return static::query()->where('location', $location)->where('is_active', true)->first();
    }

    /**
     * Items to render on the public site. When no menu record exists yet the
     * built-in default structure is used; when a record exists but is deactivated
     * nothing is rendered for that location.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function frontendItems(string $location): array
    {
        $menu = static::query()->where('location', $location)->first();

        if (! $menu) {
            return static::defaultItems($location);
        }

        return $menu->is_active ? ($menu->items ?? []) : [];
    }

    /**
     * The menu structure currently used by the public frontend, used both as the
     * seeded default and as a fallback when no menu record exists yet.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function defaultItems(string $location): array
    {
        return match ($location) {
            self::LOCATION_MOBILE => self::mobileItems(),
            self::LOCATION_FOOTER => self::footerItems(),
            default => self::primaryItems(),
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected static function primaryItems(): array
    {
        return [
            ['label' => 'Home', 'url' => '/', 'description' => null, 'icon' => null, 'color' => null, 'mega' => null, 'target' => null, 'children' => []],
            [
                'label' => 'About GoCare', 'url' => '/about', 'description' => 'Our story, values & recognition',
                'icon' => 'info', 'color' => 'indigo', 'mega' => 'standard', 'target' => null,
                'children' => [
                    ['label' => 'About GoCare Institute', 'url' => '/about', 'description' => 'Our history & leadership', 'icon' => 'home', 'color' => 'indigo'],
                    ['label' => 'Mission & Vision', 'url' => '/about#vision', 'description' => 'What drives us forward', 'icon' => 'flag', 'color' => 'teal'],
                    ['label' => 'Core Values', 'url' => '/about#core-values', 'description' => 'Principles we live by', 'icon' => 'heart', 'color' => 'rose'],
                    ['label' => 'Accreditation & Recognition', 'url' => '/about/accreditation', 'description' => 'Nationally & internationally recognised', 'icon' => 'globe', 'color' => 'purple'],
                    ['label' => 'Why Choose GoCare', 'url' => '/about/why-choose-us', 'description' => 'Stand-out reasons to join us', 'icon' => 'check-circle', 'color' => 'amber'],
                ],
            ],
            [
                'label' => 'Schools & Programs', 'url' => '/courses', 'description' => 'Explore our diverse range of courses',
                'icon' => 'graduation-cap', 'color' => 'indigo', 'mega' => 'standard', 'target' => null,
                'children' => [
                    ['label' => 'School of Medical & Health Sciences', 'url' => '/schools/medical-health-sciences', 'description' => 'Healthcare & clinical programs', 'icon' => 'stethoscope', 'color' => 'rose'],
                    ['label' => 'School of Hospitality Management', 'url' => '/schools/hospitality-management', 'description' => 'Tourism, food & front office', 'icon' => 'utensils', 'color' => 'amber'],
                    ['label' => 'School of Social Sciences & Business', 'url' => '/schools/social-sciences-business', 'description' => 'Community & business programs', 'icon' => 'users', 'color' => 'indigo'],
                    ['label' => 'International Certifications', 'url' => '/schools/international-certifications', 'description' => 'Globally recognised qualifications', 'icon' => 'globe', 'color' => 'teal'],
                    ['label' => 'View All Courses', 'url' => '/courses', 'description' => 'Browse the full programme list', 'icon' => 'search', 'color' => 'purple'],
                ],
            ],
            [
                'label' => 'Admissions', 'url' => '/admissions#overview', 'description' => 'Your journey to GoCare starts here',
                'icon' => 'clipboard-list', 'color' => 'indigo', 'mega' => 'standard', 'target' => null,
                'children' => [
                    ['label' => 'Admissions Overview', 'url' => '/admissions#overview', 'description' => 'Everything you need to know', 'icon' => 'info', 'color' => 'indigo'],
                    ['label' => 'How to Apply', 'url' => '/admissions#how-to-apply', 'description' => 'Step-by-step application guide', 'icon' => 'file-edit', 'color' => 'teal'],
                    ['label' => 'Intakes & Deadlines', 'url' => '/admissions#intakes', 'description' => 'Upcoming intake dates', 'icon' => 'calendar', 'color' => 'amber'],
                    ['label' => 'Entry Requirements', 'url' => '/admissions#requirements', 'description' => 'Academic & age criteria', 'icon' => 'clipboard-list', 'color' => 'orange'],
                    ['label' => 'Fees & Payment Options', 'url' => '/admissions#fees', 'description' => 'Tuition, HELB & bursaries', 'icon' => 'wallet', 'color' => 'purple'],
                    ['label' => 'Admissions Support', 'url' => '/admissions#support', 'description' => 'Get help from our team', 'icon' => 'headphones', 'color' => 'rose'],
                    ['label' => 'Modes of Study', 'url' => '/modes-of-study', 'description' => 'Full-time, part-time & online', 'icon' => 'book-open', 'color' => 'teal'],
                    ['label' => 'Hostels & Accommodation', 'url' => '/hostels-and-accommodation', 'description' => 'Comfortable student housing', 'icon' => 'bed', 'color' => 'amber'],
                ],
            ],
            [
                'label' => 'Industry Liaison', 'url' => '/industry-liaison', 'description' => 'Partnerships, internships & career services',
                'icon' => 'briefcase', 'color' => 'indigo', 'mega' => 'standard', 'target' => null,
                'children' => [
                    ['label' => 'Our Role', 'url' => '/industry-liaison#our-role', 'description' => 'Bridging education & industry', 'icon' => 'globe', 'color' => 'indigo'],
                    ['label' => 'Internship & Attachment', 'url' => '/industrial-attachment', 'description' => 'Hands-on industry placements', 'icon' => 'clipboard-list', 'color' => 'orange'],
                    ['label' => 'Institutional Partnerships', 'url' => '/institutional-partnerships', 'description' => 'Our network of employers', 'icon' => 'handshake', 'color' => 'teal'],
                    ['label' => 'Career Services', 'url' => '/careers', 'description' => 'Jobs, placements & guidance', 'icon' => 'briefcase', 'color' => 'amber'],
                    ['label' => 'International Certification', 'url' => '/courses/amca-usa-certification', 'description' => 'USA & global qualifications', 'icon' => 'globe-2', 'color' => 'purple'],
                    ['label' => 'Alumni Network', 'url' => '/alumni-network', 'description' => 'Stay connected after graduation', 'icon' => 'users', 'color' => 'rose'],
                    ['label' => 'GoCare Health Solutions', 'url' => '/home-based-care', 'description' => 'Professional healthcare services', 'icon' => 'heart-pulse', 'color' => 'teal'],
                ],
            ],
            [
                'label' => 'Resources', 'url' => '#', 'description' => 'Everything you need to succeed at GoCare',
                'header_label' => 'Resources & Tools', 'icon' => 'layers', 'color' => 'indigo', 'mega' => 'resources', 'target' => null,
                'children' => [
                    ['label' => 'Student Resources', 'url' => '/student-resources', 'description' => 'Study materials & guides', 'icon' => 'book-open', 'color' => 'purple'],
                    ['label' => 'Downloads', 'url' => '/downloads', 'description' => 'Forms, brochures & prospectus', 'icon' => 'download', 'color' => 'orange'],
                    ['label' => 'Blogs & Articles', 'url' => '/blog', 'description' => 'Insights, tips & news', 'icon' => 'edit-3', 'color' => 'amber'],
                    ['label' => 'Student Portal', 'url' => '#', 'description' => 'Access your student account', 'icon' => 'graduation-cap', 'color' => 'indigo'],
                    ['label' => 'Success Stories', 'url' => '/student-testimonials-success-stories', 'description' => 'Graduate testimonials', 'icon' => 'message-circle', 'color' => 'teal'],
                    ['label' => 'Photo Gallery', 'url' => '/gallery', 'description' => 'Life & moments at GoCare', 'icon' => 'images', 'color' => 'rose'],
                ],
            ],
            ['label' => 'Contact Us', 'url' => '/contact', 'description' => null, 'icon' => null, 'color' => null, 'mega' => null, 'target' => null, 'children' => []],
        ];
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    protected static function mobileItems(): array
    {
        return [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'About', 'url' => '/about'],
            ['label' => 'Schools & Programs', 'url' => '/courses'],
            ['label' => 'Admissions', 'url' => '/admissions#overview'],
            ['label' => 'Industry Liaison', 'url' => '/industry-liaison'],
            ['label' => 'FAQs', 'url' => '/faqs'],
            ['label' => 'Downloads', 'url' => '/downloads'],
            ['label' => 'Home Based Care', 'url' => '/home-based-care'],
            ['label' => 'Gallery', 'url' => '/gallery'],
            ['label' => 'Events & Open Days', 'url' => '/events'],
            ['label' => 'Blog & Articles', 'url' => '/blog'],
            ['label' => 'Contact Us', 'url' => '/contact'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected static function footerItems(): array
    {
        return [
            [
                'label' => 'Schools & Programs', 'url' => null,
                'children' => [
                    ['label' => 'School of Medical & Health Sciences', 'url' => '/schools/medical-health-sciences'],
                    ['label' => 'School of Hospitality Management', 'url' => '/schools/hospitality-management'],
                    ['label' => 'School of Social Sciences & Business Management', 'url' => '/schools/social-sciences-business'],
                    ['label' => 'International Certifications', 'url' => '/schools/international-certifications'],
                ],
            ],
            [
                'label' => 'Resources', 'url' => null,
                'children' => [
                    ['label' => 'Student Resources', 'url' => '/student-resources'],
                    ['label' => 'Downloads', 'url' => '/downloads'],
                    ['label' => 'Blogs & Articles', 'url' => '/blog'],
                    ['label' => 'Testimonials & Success Stories', 'url' => '/student-testimonials-success-stories'],
                ],
            ],
            [
                'label' => 'Quick Links', 'url' => null,
                'children' => [
                    ['label' => 'Industrial & Field Attachment', 'url' => '/industrial-attachment'],
                    ['label' => 'Student Support Services', 'url' => '/student-support-services'],
                    ['label' => 'Modes of Study', 'url' => '/modes-of-study'],
                    ['label' => 'Apply Now', 'url' => '/apply'],
                    ['label' => 'Download Prospectus', 'url' => '/docs/GoCare%20Training%20Institute%20Prospectus.pdf', 'target' => '_blank'],
                    ['label' => 'Student Portal', 'url' => '#'],
                    ['label' => 'Staff Portal', 'url' => '#'],
                    ['label' => 'Hostels & Accommodation', 'url' => '/hostels-and-accommodation'],
                ],
            ],
        ];
    }
}
