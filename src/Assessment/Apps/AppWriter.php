<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;

use Edutiek\AssessmentService\Assessment\Data\TokenPurpose;
use Edutiek\AssessmentService\System\Config\Frontend;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AppWriter extends BaseApp implements RestService
{
    protected Frontend $frontend = Frontend::WRITER;

    public function handle(): never
    {
        $this->app->get('/writer/data', [$this,'getData']);
        $this->app->put('/writer/sync', [$this,'putSync']);
        $this->app->get('/writer/file/{component}/{entity}/{id}', [$this,'getFile']);
        $this->app->put('/writer/final', [$this, 'putChanges']);
        $this->app->run();
        exit;
    }

    /**
     * PUT sync coming from the app
     * Request and response are json arrays: part => component => entity => change data
     */
    public function putSync(Request $request, Response $response, array $args): Response
    {
        $this->prepare($request, $response, $args, TokenPurpose::DATA);

        $json_data = $this->rest_helper->getJsonData($request);
        $update_data = $json_data['Update'] ?? [];
        $changes_data = $json_data['Changes'] ?? [];

        $response_json = [
            'Update' => [],
            'Changes' => []
        ];

        // todo: process status data

        // process the changes
        foreach ($changes_data as $component => $component_data) {
            $bridge = $this->getBridge((string) $component);
            if ($bridge === null) {
                continue;
            }
            foreach ((array) $component_data as $type => $list) {
                $changes = array_map(fn(array $data) => new ChangeRequest(
                    (string) $data['type'] ?? '',
                    (string) $data['key'] ?? '',
                    (int) $data['last_change'] ?? 0,
                    ChangeAction::tryFrom($data['action'] ?? ''),
                    $data['payload'] ?? null
                ), $list);

                $response_json['Changes'][$component][$type] = array_map(
                    fn(ChangeResponse $response) => $response->toArray(),
                    $bridge->applyChanges($type, $changes)
                );
            }
        }

        // get data to update after changes
        foreach ($this->apis->components($this->ass_id, $this->user_id) as $component) {
            $bridge = $this->getBridge($component);
            $response_json['Update'][$component] = $bridge?->getData(false) ?? [];
        }

        $this->rest_helper->setAlive();
        $this->rest_helper->extendDataToken($response);
        return $this->rest_helper->setResponse($response, StatusCodeInterface::STATUS_OK, $response_json);
    }

}
