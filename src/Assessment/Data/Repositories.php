<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface Repositories
{
    public function alert(): AlertRepo;
    public function location(): LocationRepo;
    public function logEntry(): LogEntryRepo;
    public function permissions(): PermissionsRepo;
    public function properties(): PropertiesRepo;
    public function correctionSettings(): CorrectionSettingsRepo;
    public function corrector(): CorrectorRepo;
    public function gradeLevel(): GradeLevelRepo;
    public function orgaSettings(): OrgaSettingsRepo;
    public function pdfSettings(): PdfSettingsRepo;
    public function token(): TokenRepo;
    public function writer(): WriterRepo;
    public function disabledGroup(): DisabledGroupRepo;
}
