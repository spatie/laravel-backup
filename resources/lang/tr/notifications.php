<?php

return [
    'exception_message' => 'Hata mesajı: :message',
    'exception_trace' => 'Hata izi: :trace',
    'exception_message_title' => 'Hata mesajı',
    'exception_trace_title' => 'Hata izi',

    'backup_failed_subject' => ':application_name yedeklenemedi',
    'backup_failed_body' => 'Önemli: :application_name yedeklenirken bir hata oluştu',

    'backup_successful_subject' => ':application_name için yeni yedekleme başarılı',
    'backup_successful_subject_title' => 'Yeni yedekleme başarılı!',
    'backup_successful_body' => 'Harika haber, :disk_name adlı diskte :application_name için yeni bir yedek başarıyla oluşturuldu.',

    'cleanup_failed_subject' => ':application_name yedekleri temizlenemedi.',
    'cleanup_failed_body' => ':application_name yedekleri temizlenirken bir hata oluştu',

    'cleanup_successful_subject' => ':application_name yedekleri başarıyla temizlendi',
    'cleanup_successful_subject_title' => 'Yedekler başarıyla temizlendi!',
    'cleanup_successful_body' => ':disk_name adlı diskteki :application_name yedekleri başarıyla temizlendi.',

    'healthy_backup_found_subject' => ':disk_name adlı diskteki :application_name yedekleri sağlıklı',
    'healthy_backup_found_subject_title' => ':application_name yedekleri sağlıklı',
    'healthy_backup_found_body' => ':application_name yedekleri sağlıklı kabul ediliyor. Tebrikler!',

    'unhealthy_backup_found_subject' => 'Önemli: :application_name yedekleri sağlıksız',
    'unhealthy_backup_found_subject_title' => 'Önemli: :application_name yedekleri sağlıksız. :problem',
    'unhealthy_backup_found_body' => ':disk_name adlı diskteki :application_name yedekleri sağlıksız.',
    'unhealthy_backup_found_not_reachable' => 'Yedekleme hedefine ulaşılamıyor. :error',
    'unhealthy_backup_found_empty' => 'Bu uygulamanın hiç yedeği yok.',
    'unhealthy_backup_found_old' => ':date tarihinde alınan son yedek çok eski kabul ediliyor.',
    'unhealthy_backup_found_unknown' => 'Maalesef kesin bir neden belirlenemedi.',
    'unhealthy_backup_found_full' => 'Yedekler çok fazla depolama alanı kullanıyor. Şu anki kullanım (:disk_usage), izin verilen :disk_limit sınırının üzerinde.',

    'no_backups_info' => 'Henüz yedek alınmadı',
    'application_name' => 'Uygulama adı',
    'backup_name' => 'Yedek adı',
    'disk' => 'Disk',
    'newest_backup_size' => 'En yeni yedeğin boyutu',
    'number_of_backups' => 'Yedek sayısı',
    'total_storage_used' => 'Kullanılan toplam depolama alanı',
    'newest_backup_date' => 'En yeni yedeğin tarihi',
    'oldest_backup_date' => 'En eski yedeğin tarihi',
];
