<?php

return [
    'source_mismatch' => 'مصدر الإعلان لا يطابق مصدر عملية جمع البيانات.',
    'run_finished' => 'لا يمكن إضافة وظائف خام إلى عملية جمع بيانات منتهية.',
    'already_finalized' => 'انتهت عملية استخراج بيانات هذه الوظيفة بالفعل.',
    'normalization_requires_extraction' => 'يتطلب تطبيع الوظيفة نجاح استخراج تفاصيلها أولًا.',
    'normalization_already_finalized' => 'انتهت عملية تطبيع بيانات هذه الوظيفة بالفعل.',
    'invalid_payload' => 'يجب أن تحتوي البيانات الخام على بيانات JSON صالحة.',
    'sensitive_data' => 'لا يمكن تخزين محتوى يتضمن بيانات اعتماد ضمن بيانات الوظيفة الخام.',
    'invalid_url' => 'يجب أن يكون رابط المصدر رابط HTTP أو HTTPS صالحًا.',
    'errors' => [
        'extraction_failed' => 'فشل استخراج تفاصيل الوظيفة.',
        'invalid_payload' => 'تعذّر استخراج بيانات الوظيفة.',
        'unsupported_format' => 'تنسيق بيانات الوظيفة غير مدعوم.',
    ],
    'normalization_errors' => [
        'invalid_data' => 'تعذر تطبيع بيانات الوظيفة المستخرجة.',
        'unsupported_value' => 'قيمة الوظيفة غير مدعومة للتطبيع.',
    ],
    'deduplication_requires_normalization' => 'يتطلب إزالة تكرار الوظيفة نجاح التطبيع أولًا.',
    'deduplication_already_finalized' => 'انتهت عملية إزالة تكرار هذه الوظيفة بالفعل.',
    'reference_provenance_mismatch' => 'مصدر الإشارة المرجعية لا يطابق مصدر الوظيفة الخام.',
    'deduplication_errors' => [
        'deduplication_failed' => 'فشلت إزالة تكرار الوظيفة.',
    ],
];
