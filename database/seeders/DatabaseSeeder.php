<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Course;
use App\Models\Menu;
use App\Models\School;
use App\Models\SeoSetting;
use App\Models\SitePage;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        SeoSetting::current();

        User::updateOrCreate(['email' => env('ADMIN_EMAIL', 'admin@gocare.test')], [
            'name' => env('ADMIN_NAME', 'GoCare Administrator'),
            'password' => env('ADMIN_PASSWORD', 'change-this-password'),
            'email_verified_at' => now(),
        ]);

        Course::updateOrCreate(['slug' => 'certificate-in-home-based-care-support-level-3'], [
            'title' => 'Certificate in Home-Based Care Support - Level 3',
            'school' => 'Medical & Health Sciences',
            'summary' => 'A foundational program preparing learners for home-based care assistant, HCA, and caregiver roles.',
            'image' => 'images/new-images/HBCS.jpeg',
            'is_published' => true,
        ]);

        BlogPost::updateOrCreate(['slug' => 'home-based-care-level-3'], [
            'title' => 'Certificate in Home-Based Care Support - Level 3',
            'excerpt' => 'Explore the home-based care support training pathway at GoCare Training Institute.',
            'image' => 'images/homecare-level-3.jpg',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $slides = [
            ['title' => 'Discover Your Path to', 'accent_title' => 'Professional Excellence', 'title_suffix' => 'and Career Fulfilment!', 'image' => 'images/new-images/IMG_2910.JPG', 'alt_text' => 'Discover Your Path to Professional Excellence', 'primary_label' => 'Apply Now', 'primary_url' => 'apply', 'secondary_label' => 'Download Prospectus', 'secondary_url' => 'docs/GoCare%20Training%20Institute%20Prospectus.pdf', 'secondary_style' => 'ghost', 'open_new_tab' => true, 'sort_order' => 1],
            ['title' => 'Empowering Professions', 'accent_title' => 'Through Expert-Led Training', 'image' => 'images/new-images/slider/EMPOWERING PROFESSIONS THROUGH EXPERT-LED TRAINING.jpeg', 'alt_text' => 'Empowering Professions Through Expert-Led Training', 'primary_label' => 'Apply Now', 'primary_url' => 'apply', 'secondary_label' => 'Explore Courses', 'secondary_url' => 'courses', 'secondary_style' => 'ghost', 'sort_order' => 2],
            ['title' => 'Guaranteed Industrial Attachment', 'accent_title' => 'for Every GoCare Student', 'image' => 'images/new-images/slider/GUARANTEED INDUSTRIAL ATTACHMENT FOR EVERY GOCARE STUDENT.JPG.jpeg', 'alt_text' => 'Guaranteed Industrial Attachment for Every GoCare Student', 'primary_label' => 'Learn More', 'primary_url' => 'industrial-attachment', 'secondary_label' => 'Apply Now', 'secondary_url' => 'apply', 'secondary_style' => 'ghost', 'sort_order' => 3],
            ['title' => 'Inspiring Tomorrow\'s Experts', 'accent_title' => 'Through Innovation', 'image' => 'images/new-images/slider/INSPIRING TOMORROW\'S EXPERTS THROUGH INNOVATION.JPG.jpeg', 'alt_text' => 'Inspiring Tomorrow\'s Experts Through Innovation', 'primary_label' => 'Apply Now', 'primary_url' => 'apply', 'secondary_label' => 'Explore Courses', 'secondary_url' => 'courses', 'sort_order' => 4],
            ['title' => 'Train With The Experts&ndash;', 'accent_title' => 'Become an Expert!', 'image' => 'images/new-images/slider/TRAIN WITH THE EXPERTS... BECOME AN EXPERT!...jpg.jpeg', 'alt_text' => 'Train With The Experts Become an Expert', 'primary_label' => 'Apply Now', 'primary_url' => 'apply', 'secondary_label' => 'Download Prospectus', 'secondary_url' => 'docs/GoCare%20Training%20Institute%20Prospectus.pdf', 'secondary_style' => 'ghost', 'open_new_tab' => true, 'sort_order' => 5],
            ['title' => 'Your Gateway to', 'accent_title' => 'International Certifications', 'image' => 'images/new-images/slider/YOUR GATE WAY TO INTERNATIONAL CERTIFICATIONS.jpg.jpeg', 'alt_text' => 'Your Gateway to International Certifications', 'primary_label' => 'View Accredited Courses', 'primary_url' => 'courses', 'secondary_label' => 'Start Your Journey', 'secondary_url' => 'apply', 'secondary_style' => 'ghost', 'sort_order' => 6],
            ['title' => 'Intakes &', 'accent_title' => 'Deadlines', 'description' => 'Stay informed about current and upcoming intakes - plan your enrollment early.', 'highlight' => 'Registration & Admission for the Upcoming Intake is Ongoing - Apply Now!', 'points' => ['Multiple intakes throughout the year for different schedules.', 'Admissions open on a rolling basis for select programs.', 'Apply early to secure your place in the next intake.'], 'image' => 'images/new-images/Admissions-hero.jpeg', 'alt_text' => 'Intakes and Deadlines', 'primary_label' => 'Apply Now', 'primary_url' => 'apply', 'secondary_label' => 'View Programs', 'secondary_url' => 'courses', 'sort_order' => 7],
            ['title' => 'Entry', 'accent_title' => 'Requirements', 'description' => 'At GoCare Training Institute, every learner has a pathway to success.', 'points' => ['Any KCSE mean grade qualifies for a course.', 'Different courses have different entry requirements.', 'KCPE certificate holders also qualify.', 'Visit individual course pages for specific requirements.'], 'image' => 'images/new-images/slider/slider-img-new.jpeg', 'alt_text' => 'Entry Requirements', 'primary_label' => 'Apply Now', 'primary_url' => 'apply', 'secondary_label' => 'Explore Courses', 'secondary_url' => 'courses', 'sort_order' => 8],
        ];

        foreach ($slides as $slide) {
            Slider::updateOrCreate(['sort_order' => $slide['sort_order']], $slide);
        }

        $content = json_decode(file_get_contents(database_path('seeders/data/content.json')), true, flags: JSON_THROW_ON_ERROR);
        $extractSections = static function (string $body): array {
            preg_match_all('/<h[1-6][^>]*>(.*?)<\/h[1-6]>(.*?)(?=<h[1-6][^>]*>|$)/is', $body, $matches, PREG_SET_ORDER);
            $sections = [];

            foreach ($matches as $match) {
                $heading = trim(html_entity_decode(strip_tags($match[1])));
                $text = trim(html_entity_decode(preg_replace('/\s+/', ' ', strip_tags($match[2]))));
                if ($heading || $text) {
                    $sections[] = ['heading' => $heading ?: 'Content', 'body' => $text];
                }
            }

            return $sections ?: [['heading' => 'Content', 'body' => trim(html_entity_decode(preg_replace('/\s+/', ' ', strip_tags($body))))]];
        };

        foreach ($content as $record) {
            $body = is_array($record['content']) ? implode("\n", $record['content']) : (string) $record['content'];
            $attributes = [
                'title' => $record['title'],
                'content' => $body,
                'sections' => $extractSections($body),
                'is_published' => true,
            ];

            if ($record['type'] === 'blog') {
                BlogPost::updateOrCreate(['slug' => $record['slug']], $attributes + [
                    'published_at' => now(),
                ]);
            } elseif ($record['type'] === 'course') {
                Course::updateOrCreate(['slug' => $record['slug']], $attributes + [
                    'school' => null,
                ]);
            } elseif ($record['type'] === 'school') {
                School::updateOrCreate(['source_path' => $record['source_path']], [
                    'title' => $record['title'],
                    'slug' => $record['slug'],
                    'content' => $body,
                    'sections' => $extractSections($body),
                    'source_path' => $record['source_path'],
                    'is_published' => true,
                ]);
            } elseif ($record['type'] === 'page') {
                $pageImages = [
                    'about' => 'images/new-images/WhatsApp Image 2026-03-26 at 11.59.07 (1).jpeg',
                    'admissions' => 'images/new-images/Admissions-hero.jpeg',
                    'alumni-network' => 'images/new-images/PXL_20260523_105012660.MP~2.jpg.jpeg',
                    'careers' => 'images/new-images/PXL_20260407_060321091.MP~2.jpg',
                    'contact' => 'images/new-images/contact-us-header.png',
                    'faqs' => 'images/faqs-hero.jpg',
                    'industry-liaison' => 'images/new-images/IMG-20210922-WA0005.jpg.jpeg',
                    'student-resources' => 'images/new-images/PXL_20260218_064240333.MP~2.jpg',
                    'student-support-services' => 'images/new-images/IMG_5629.jpeg',
                    'modes-of-study' => 'images/new-images/6.jpg',
                    'events' => 'images/events-hero.jpg',
                    'downloads' => 'images/new-images/WhatsApp Image 2026-05-05 at 18.39.10.jpeg',
                    'home-based-care' => 'images/female-nurse-portrait-with-older-patient.jpg',
                    'hostels-and-accommodation' => 'images/new-images/IMG_2063.jpg',
                ];

                SitePage::updateOrCreate(['source_path' => $record['source_path']], [
                    'title' => $record['title'],
                    'slug' => $record['slug'],
                    'content' => $body,
                    'sections' => $extractSections($body),
                    'image' => $pageImages[$record['slug']] ?? null,
                    'use_builder' => false,
                    'source_path' => $record['source_path'],
                    'is_published' => true,
                ]);
            }
        }

        SitePage::updateOrCreate(['source_path' => 'industry-liaison-redesign.html'], [
            'title' => 'Industry Liaison Redesign',
            'slug' => 'industry-liaison-redesign',
            'content' => file_get_contents(resource_path('views/pages/industry-liaison-redesign.blade.php')),
            'sections' => [['heading' => 'Industry Liaison Redesign', 'body' => 'Industry liaison content section']],
            'use_builder' => false,
            'source_path' => 'industry-liaison-redesign.html',
            'is_published' => true,
        ]);

        $menuMeta = [
            Menu::LOCATION_PRIMARY => ['name' => 'Primary Navigation', 'description' => 'Header navigation with mega menus.'],
            Menu::LOCATION_MOBILE => ['name' => 'Mobile Menu', 'description' => 'Drawer menu shown on small screens.'],
            Menu::LOCATION_FOOTER => ['name' => 'Footer', 'description' => 'Footer link columns.'],
        ];

        foreach ($menuMeta as $location => $meta) {
            Menu::updateOrCreate(['location' => $location], [
                'name' => $meta['name'],
                'description' => $meta['description'],
                'is_active' => true,
                'items' => Menu::defaultItems($location),
            ]);
        }
    }
}
