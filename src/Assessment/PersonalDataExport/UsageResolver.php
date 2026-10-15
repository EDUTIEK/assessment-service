<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport;

interface UsageResolver
{
    public function componentId(): string;

    /**
     * PHASE 1: Wo wird u_id (im gegebenen Kontext) in dieser Komponente
     * verwendet? Liefert nur Scopes, keine Fachdaten.
     *
     * @return UsageScope[]
     */
    public function resolveUsages(UsageQuery $query): array;

    /**
     * PHASE 2: Fachdaten für einen Scope dieser Komponente.
     * @return UsageNode
     * @var UsageNode[] $sub_nodes
     */
    public function collectData(UsageScope $scope, array $sub_nodes): UsageNode;

    /**
     * Kompletter Sammeldurchlauf DIESER Komponente inkl. Delegation:
     * löst eigene Scopes auf, sammelt eigene Pakete und ruft die
     * Resolver der nächsten Ebene aus $query->steps selbst auf.
     *
     * @return UsageNode[]
     */
    public function collectUsage(UsageQuery $query): array;
}