<?php
return [
    'db_host' => getenv('DB_HOST') ?: '127.0.0.1',
    'db_name' => getenv('DB_NAME') ?: 'ux_tools',
    'db_user' => getenv('DB_USER') ?: 'root',
    'db_pass' => getenv('DB_PASS') ?: '',
    'base_url' => getenv('BASE_URL') ?: '',
    'session_name' => getenv('SESSION_NAME') ?: 'ux_tools_session',
    'mail_from' => getenv('MAIL_FROM') ?: 'noreply@local.test',
];
