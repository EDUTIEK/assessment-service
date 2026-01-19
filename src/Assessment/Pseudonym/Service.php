<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Pseudonym;

use Edutiek\AssessmentService\System\Language\Service as LanguageService;
use Edutiek\AssessmentService\System\User\Service as UserService;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\Pseudonymization;
use Edutiek\AssessmentService\System\Data\UserData;

class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private LanguageService $language_service,
        private UserService $user_service,
    ) {
    }

    public function options(): array
    {
        return [
            Pseudonymization::WRITER_ID->value => $this->language_service->txt('pseudonymization_writer_id'),
            Pseudonymization::USER_ID->value => $this->language_service->txt('pseudonymization_user_id'),
            Pseudonymization::LOGIN->value => $this->language_service->txt('pseudonymization_login'),
            Pseudonymization::MATRICULATION->value => $this->language_service->txt('pseudonymization_matriculation'),
            Pseudonymization::NAME->value => $this->language_service->txt('pseudonymization_name'),
        ];
    }

    public function changeForAll(Pseudonymization $pseudonymisation): void
    {
        $writers = $this->repos->writer()->allByAssId($this->ass_id);
        $user_ids = array_map(fn($writer) => $writer->getUserId(), $writers);

        $users = [];
        foreach ($this->user_service->getUsersByIds($user_ids) as $user) {
            $users[$user->getId()] = $user;
        };

        foreach ($writers as $writer) {
            if ($users[$writer->getUserId()] ?? null) {
                $writer->setPseudonym($this->build($writer->getId(), $users[$writer->getUserId()], $pseudonymisation));
                $this->repos->writer()->save($writer);
            }
        }
    }

    public function buildForWriter(int $id, int $user_id): string
    {
        $settings = $this->repos->correctionSettings()->one($this->ass_id)
            ?? $this->repos->correctionSettings()->new();

        return $this->build($id, $this->user_service->getUser($user_id), $settings->getPseudonymization());
    }

    private function build(int $writer_id, ?UserData $user, Pseudonymization $pseudonymization)
    {
        if ($user === null) {
            $pseudonymization = Pseudonymization::WRITER_ID;
        }

        switch ($pseudonymization) {
            case Pseudonymization::MATRICULATION:
                return !empty($user->getMatriculation()) ? $user->getMatriculation() : $user->getLogin();
            case Pseudonymization::NAME:
                return $user->getListname(false);
            case Pseudonymization::LOGIN:
                return $user->getLogin();
            case Pseudonymization::USER_ID:
                return $this->language_service->txt('user_x', ['x' => (string) $writer_id]);
            case Pseudonymization::WRITER_ID:
            default:
                return $this->language_service->txt('writer_x', ['x' => (string) $writer_id]);
        }
    }
}
