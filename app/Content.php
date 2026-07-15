<?php
namespace App;

/**
 * Institute showcase content (faculty, awards, facilities, stats, values).
 * Kept as structured data so the same source feeds both the home-page teasers
 * and the dedicated About / Faculty / Campus / Awards pages.
 */
class Content
{
    public static function stats(): array
    {
        return [
            ['10+',    'Years of Excellence'],
            ['1,200+', 'Students Trained'],
            ['9',      'Professional Courses'],
            ['95%',    'Course Satisfaction'],
        ];
    }

    public static function faculty(): array
    {
        return [
            [
                'name' => 'Engr. Imran Khan', 'role' => 'Founder & Lead Instructor',
                'photo' => 'faculty/1.jpg', 'tag' => 'Networking & Cisco',
                'expertise' => ['CCNA / CCNP', 'Routing & Switching', 'Network Security', 'Network Automation'],
                'bio' => 'Founder of IT Training Institute and the face of our free YouTube channel. Over a decade of hands-on networking experience, training the next generation of Cisco engineers in Kumber Maidan.',
                'years' => '10+ yrs',
            ],
            [
                'name' => 'Ahmed Raza', 'role' => 'Cyber Security & Ethical Hacking',
                'photo' => 'faculty/3.jpg', 'tag' => 'Cyber Security',
                'expertise' => ['Ethical Hacking', 'Penetration Testing', 'OWASP / Web Security', 'Incident Response'],
                'bio' => 'A security professional who teaches authorised, defensive hacking through real labs - turning curious students into responsible security practitioners.',
                'years' => '8+ yrs',
            ],
            [
                'name' => 'Bilal Hussain', 'role' => 'Programming Lead',
                'photo' => 'faculty/2.jpg', 'tag' => 'Programming',
                'expertise' => ['C++ & OOP', 'Java', 'Python', 'Problem Solving'],
                'bio' => 'Makes programming click for absolute beginners. From first "Hello World" to building real projects, Bilal mentors students into confident developers.',
                'years' => '7+ yrs',
            ],
            [
                'name' => 'Ayesha Siddiqui', 'role' => 'Web Development Instructor',
                'photo' => 'faculty/4.jpg', 'tag' => 'Web Development',
                'expertise' => ['HTML & CSS', 'Responsive Design', 'Front-end Basics', 'UI Fundamentals'],
                'bio' => 'Guides students through their first websites with patience and clarity, building strong web foundations from the ground up.',
                'years' => '5+ yrs',
            ],
            [
                'name' => 'Usman Ali', 'role' => 'CCTV & Hardware Specialist',
                'photo' => 'faculty/5.jpg', 'tag' => 'Hardware & CCTV',
                'expertise' => ['CCTV Installation', 'DVR / NVR Config', 'IP Cameras', 'Cabling & Tools'],
                'bio' => 'A field expert who teaches CCTV and hardware networking through practical, job-ready training - so students can start earning quickly.',
                'years' => '9+ yrs',
            ],
            [
                'name' => 'Fatima Noor', 'role' => 'Student Affairs & Career Counsellor',
                'photo' => 'faculty/6.jpg', 'tag' => 'Career Support',
                'expertise' => ['Admissions Guidance', 'Career Counselling', 'Interview Prep', 'Student Support'],
                'bio' => 'Helps students choose the right course and supports them all the way to certification and their first job or freelance work.',
                'years' => '6+ yrs',
            ],
        ];
    }

    public static function awards(): array
    {
        return [
            ['year' => '2024', 'title' => 'Top Tech Educator on YouTube', 'org' => 'Community Recognition',
             'image' => 'awards/award-1.jpg', 'desc' => 'Recognised for free, high-quality IT lessons reaching thousands of learners across Pakistan.'],
            ['year' => '2023', 'title' => 'Best IT Training Institute', 'org' => 'Kumber Maidan Business Forum',
             'image' => 'awards/award-2.jpg', 'desc' => 'Awarded for excellence in affordable, job-oriented IT education in the region.'],
            ['year' => '2022', 'title' => 'Excellence in Skill Development', 'org' => 'District Recognition',
             'image' => 'awards/award-3.jpg', 'desc' => 'Honoured for equipping local youth with industry-ready technical skills.'],
            ['year' => '2021', 'title' => '1,000 Students Milestone', 'org' => 'Institute Achievement',
             'image' => 'awards/award-4.jpg', 'desc' => 'Celebrated training over one thousand students in networking, security and programming.'],
        ];
    }

    public static function facilities(): array
    {
        return [
            ['title' => 'Modern Computer Lab', 'image' => 'photos/lab.jpg', 'desc' => 'Fully-equipped systems for every student to practice hands-on, every class.'],
            ['title' => 'Networking & Cisco Lab', 'image' => 'courses/ccna-200-301.jpg', 'desc' => 'Real routers, switches and gear for live CCNA configuration practice.'],
            ['title' => 'CCTV & Hardware Workshop', 'image' => 'courses/cctv-camera-installation.jpg', 'desc' => 'A practical workshop to learn camera installation, cabling and DVR/NVR setup.'],
            ['title' => 'Smart Classrooms', 'image' => 'photos/about.jpg', 'desc' => 'Comfortable, projector-equipped classrooms designed for focused learning.'],
            ['title' => 'Study & Library Area', 'image' => 'photos/campus-3.jpg', 'desc' => 'A quiet space with resources for self-study and revision.'],
            ['title' => 'High-Speed Internet', 'image' => 'photos/campus-5.jpg', 'desc' => 'Reliable connectivity for labs, research and online practice.'],
        ];
    }

    public static function gallery(): array
    {
        return ['photos/campus-1.jpg','photos/campus-2.jpg','photos/campus-3.jpg',
                'photos/campus-4.jpg','photos/campus-5.jpg','photos/campus-6.jpg',
                'photos/lab.jpg','photos/about.jpg'];
    }

    public static function values(): array
    {
        return [
            ['icon' => 'target', 'title' => 'Job-Ready Skills', 'desc' => 'Practical, hands-on training focused on real-world employability - not just theory.'],
            ['icon' => 'money', 'title' => 'Affordable Fees', 'desc' => 'Quality IT education priced for local students, with free previews on every course.'],
            ['icon' => 'certificate', 'title' => 'Verifiable Certificates', 'desc' => 'Chapter and course certificates you can verify online and add to your CV.'],
            ['icon' => 'play', 'title' => 'Free YouTube Learning', 'desc' => 'Learn the basics free on our channel before you ever pay a rupee.'],
            ['icon' => 'teacher', 'title' => 'Expert Instructors', 'desc' => 'Certified, experienced teachers who mentor you personally.'],
            ['icon' => 'lock', 'title' => 'Secure Learning', 'desc' => 'Protected lessons and a device-locked account that keeps your access safe.'],
        ];
    }
}
