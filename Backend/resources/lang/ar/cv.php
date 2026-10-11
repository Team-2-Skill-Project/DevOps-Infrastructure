<?php

return [
    'uploaded' => 'تم رفع السيرة الذاتية بنجاح وإضافتها إلى قائمة انتظار المعالجة.',
    'processing_retried' => 'تمت إعادة محاولة معالجة السيرة الذاتية بنجاح.',
    'extraction_verified' => 'تم التحقق من البيانات المستخرجة ومزامنتها مع الملف الشخصي بنجاح.',
    'candidate_profile_not_found' => 'لم يتم العثور على ملف المرشح الشخصي.',
    'document_not_found' => 'لم يتم العثور على السيرة الذاتية.',
    'extraction_not_found' => 'لم يتم العثور على بيانات السيرة الذاتية المستخرجة.',
    'forbidden' => 'غير مصرح لك بالوصول إلى هذه السيرة الذاتية.',
    'validation' => [
        'empty_file' => 'الملف المرفوع فارغ ولا يحتوي على أي بيانات.',
    ],
    'attributes' => [
        'cv' => 'السيرة الذاتية',
        'skills' => 'المهارات',
        'skills.*.name' => 'اسم المهارة',
        'skills.*.category' => 'تصنيف المهارة',
        'skills.*.proficiency_level' => 'مستوى إتقان المهارة',
        'skills.*.confidence_score' => 'درجة الثقة في المهارة',
        'experiences' => 'الخبرات',
        'experiences.*.company_name' => 'اسم الشركة',
        'experiences.*.title' => 'المسمى الوظيفي',
        'experiences.*.start_date' => 'تاريخ بدء الخبرة',
        'experiences.*.end_date' => 'تاريخ انتهاء الخبرة',
        'experiences.*.description' => 'وصف الخبرة',
    ],
    // Compatibility aliases for keys introduced upstream.
    'profile_not_found' => 'لم يتم العثور على ملف المرشح.',
    'uploaded_success' => 'تم رفع السيرة الذاتية بنجاح وهي قيد المعالجة.',
    'retried_success' => 'تم إعادة محاولة معالجة السيرة الذاتية بنجاح.',
    'verified_success' => 'تم التحقق من البيانات المستخرجة ومزامنتها مع البروفايل بنجاح.',
];
