<?php
declare(strict_types=1);

auth_logout();
header('Location: ?p=login');
exit;
