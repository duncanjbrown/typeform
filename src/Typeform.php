<?php
namespace WATR;

use GuzzleHttp\Client;
use WATR\Models\Form;
use WATR\Models\FormResponse;
use WATR\Models\WebhookResponse;

/**
 * Base Package wrapper for Typeform API
 */
class Typeform
{
    /**
     * @var  GuzzleHttp\Client
     */
    protected $http;

    /**
     * @var  string Typeform API key
     */
    protected $apiKey;

    /**
     * @var string Typeform base URI
     */
    protected $baseUri = 'https://api.typeform.com/';

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->http = new Client([
            'base_uri' => $this->baseUri,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
            ]
        ]);
    }

    /**
     * Get form information
     */
    public function getForm($formId)
    {
        $response = $this->http->get("/forms/" . $formId);
        $body = json_decode($response->getBody());
        return new Form($body);
    }

    /**
     * Get form responses
     */
    public function getResponses($formId, $params = [])
    {
        $path = "/forms/" . $formId . "/responses";
        $query_string = http_build_query($params);

        $path_and_query = implode('?', [$path, $query_string]);

        $response = $this->http->get($path_and_query);
        $body = json_decode($response->getBody());
        $responses = [];
        if (isset($body->items)) {
            foreach ($body->items as $item) {
                $responses[] = new FormResponse($item);
            }
        }
        return $responses;
    }

    /**
     * Register webhook for form
     */
    public function registerWebhook(Form $form, string $url, string $tag = "response")
    {
        $response = $this->http->put(
            "/forms/" . $form->id . "/webhooks/" . $tag,
            [
                'json' => [
                    'url' => $url,
                    'enabled' => true,
                ]
            ]
        );
        return json_decode($response->getBody());
    }


    public function addHiddenFields(Form $form, $fields)
    {
        $form->addHiddenFields($fields);

        $response = $this->http->put(
            "/forms/" . $form->id,
            [
              'json' => (array) $form->getRaw(),
            ]
        );
    }

    /**
     * Parse incoming webhook
     */
    public static function parseWebhook($json)
    {
        return new WebhookResponse($json);
    }
}
