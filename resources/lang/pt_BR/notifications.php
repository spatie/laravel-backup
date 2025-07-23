<?php

return [
    'exception_message' => 'Mensagem de exceção: :message',
    'exception_trace' => 'Rastreamento de exceção: :trace',
    'exception_message_title' => 'Mensagem de exceção',
    'exception_trace_title' => 'Rastreamento de exceção',

    'backup_failed_subject' => 'Falha no backup da aplicação :application_name',
    'backup_failed_body' => 'Importante: Ocorreu um erro ao fazer o backup da aplicação :application_name',

    'backup_successful_subject' => 'Backup realizado com sucesso: :application_name',
    'backup_successful_subject_title' => 'Backup Realizado com sucesso!',
    'backup_successful_body' => 'Boas notícias, um novo backup da aplicação :application_name foi criado no disco :disk_name.',

    'cleanup_failed_subject' => 'Falha na limpeza dos backups da aplicação :application_name.',
    'cleanup_failed_body' => 'Um erro ocorreu ao fazer a limpeza dos backups da aplicação :application_name',

    'cleanup_successful_subject' => 'Limpeza dos backups da aplicação :application_name concluída!',
    'cleanup_successful_subject_title' => 'Limpeza dos backups concluída!',
    'cleanup_successful_body' => 'A limpeza dos backups da aplicação :application_name no disco :disk_name foi concluída.',

    'healthy_backup_found_subject' => 'Os backups da aplicação :application_name no disco :disk_name estão em dia',
    'healthy_backup_found_subject_title' => 'Os backups da aplicação :application_name estão em dia',
    'healthy_backup_found_body' => 'Os backups da aplicação :application_name estão em dia. Bom trabalho!',

    'unhealthy_backup_found_subject' => 'Importante: Os backups da aplicação :application_name não estão em dia',
    'unhealthy_backup_found_subject_title' => 'Importante: Os backups da aplicação :application_name não estão em dia. :problem',
    'unhealthy_backup_found_body' => 'Os backups da aplicação :application_name no disco :disk_name não estão em dia.',
    'unhealthy_backup_found_not_reachable' => 'O destino dos backups não pode ser alcançado. :error',
    'unhealthy_backup_found_empty' => 'Não existem backups para essa aplicação.',
    'unhealthy_backup_found_old' => 'O último backup realizado em :date é considerado muito antigo.',
    'unhealthy_backup_found_unknown' => 'Desculpe, a exata razão não pode ser encontrada.',
    'unhealthy_backup_found_full' => 'Os backups estão usando muito espaço de armazenamento. A utilização atual é de :disk_usage, o que é maior que o limite permitido de :disk_limit.',

    'no_backups_info' => 'Nenhum backup foi feito ainda',
    'application_name' => 'Nome da Aplicação',
    'backup_name' => 'Nome de backup',
    'disk' => 'Disco',
    'newest_backup_size' => 'Tamanho do backup mais recente',
    'number_of_backups' => 'Número de backups',
    'total_storage_used' => 'Armazenamento total usado',
    'newest_backup_date' => 'Data do backup mais recente',
    'oldest_backup_date' => 'Data do backup mais antigo',
];
