<?php

return [
    'exception_message' => 'Қате туралы хабарлама: :message',
    'exception_trace' => 'Қате туралы мәліметтер: :trace',
    'exception_message_title' => 'Қате туралы хабарлама',
    'exception_trace_title' => 'Қате туралы мәліметтер',

    'backup_failed_subject' => ':application_name бағдарламасының резервтік көшірмесін жасау сәтсіз аяқталды',
    'backup_failed_body' => 'Маңызды: :application_name бағдарламасының резервтік көшірмесін жасау барысында қате орын алды',

    'backup_successful_subject' => ':application_name бағдарламасының жаңа резервтік көшірмесі сәтті құрылды',
    'backup_successful_subject_title' => 'Жаңа резервтік көшірме сәтті құрылды!',
    'backup_successful_body' => 'Жақсы жаңалық: :application_name бағдарламасының жаңа резервтік көшірмесі :disk_name дискінде сәтті құрылды.',

    'cleanup_failed_subject' => ':application_name бағдарламасының резервтік көшірмелерін тазалау сәтсіз аяқталды',
    'cleanup_failed_body' => ':application_name бағдарламасының резервтік көшірмелерін тазалау барысында қате орын алды',

    'cleanup_successful_subject' => ':application_name бағдарламасының резервтік көшірмелерін тазалау сәтті өтті',
    'cleanup_successful_subject_title' => 'Резервтік көшірмелерді тазалау сәтті аяқталды!',
    'cleanup_successful_body' => ':disk_name дискіндегі :application_name бағдарламасының резервтік көшірмелерін тазалау сәтті аяқталды.',

    'healthy_backup_found_subject' => ':disk_name дискіндегі :application_name бағдарламасының резервтік көшірмелері қалыпты күйде',
    'healthy_backup_found_subject_title' => ':application_name бағдарламасының резервтік көшірмелері қалыпты күйде',
    'healthy_backup_found_body' => ':application_name бағдарламасының резервтік көшірмелері толық тексеруден өтті. Өте жақсы!',

    'unhealthy_backup_found_subject' => 'Маңызды: :application_name бағдарламасының резервтік көшірмелері жарамсыз күйде',
    'unhealthy_backup_found_subject_title' => 'Маңызды: :application_name бағдарламасының резервтік көшірмелері жарамсыз күйде. :problem',
    'unhealthy_backup_found_body' => ':disk_name дискіндегі :application_name бағдарламасының резервтік көшірмелері жарамсыз күйде.',
    'unhealthy_backup_found_not_reachable' => 'Резервтік көшірме сақтау орнына қол жеткізу мүмкін емес. :error',
    'unhealthy_backup_found_empty' => 'Осы бағдарлама бойынша резервтік көшірмелер әлі жасалмаған.',
    'unhealthy_backup_found_old' => 'Соңғы резервтік көшірме (:date) тым ескі болып саналады.',
    'unhealthy_backup_found_unknown' => 'Кешіріңіз, нақты себебін анықтау мүмкін емес.',
    'unhealthy_backup_found_full' => 'Резервтік көшірмелер тым көп орын алып отыр. Ағымдағы пайдалану көлемі :disk_usage, бұл рұқсат етілген шектен :disk_limit аса жоғары.',

    'no_backups_info' => 'Әлі резервтік көшірме жасалмаған',
    'application_name' => 'Бағдарлама атауы',
    'backup_name' => 'Резервтік көшірме атауы',
    'disk' => 'Диск',
    'newest_backup_size' => 'Соңғы резервтік көшірменің көлемі',
    'number_of_backups' => 'Резервтік көшірмелер саны',
    'total_storage_used' => 'Жалпы қолданылған сақтау көлемі',
    'newest_backup_date' => 'Соңғы резервтік көшірме күні',
    'oldest_backup_date' => 'Ең ескі резервтік көшірме күні',
];
