<?php

namespace App\Models;

use OwenIt\Auditing\Models\Audit as BaseAudit;

class Audit extends BaseAudit
{
    // linha de auditoria nunca é editada: só created_at
    const UPDATED_AT = null;
}
