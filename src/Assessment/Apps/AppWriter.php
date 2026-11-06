<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;

use Edutiek\AssessmentService\System\Config\Frontend;
use Edutiek\AssessmentService\System\Rest\RestHandler;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AppWriter extends BaseApp implements RestHandler
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
        $this->prepare($request, $response, $args);

        $json_data = $this->rest_helper->getJsonData($request);

        $response_json = [
            'Update' => [],
            'Changes' => []
        ];

        // process the changes
        foreach ($json_data as $component => $component_data) {
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
            $response_json['Update'][$component] = $bridge?->getData(true) ?? [];
        }

        return $this->rest_helper->setJsonResponse($response, StatusCodeInterface::STATUS_OK, $response_json);
    }

}
