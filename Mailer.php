<?php
/**
 * Contains the Mailer class
 *
 * @package weluse/mailjet
 */

namespace weluse\mailjet;

use Mailjet\Resources;
use yii\base\InvalidConfigException;
use yii\base\UserException;
use yii\mail\BaseMailer;
use yii\validators\UrlValidator;

class Mailer extends BaseMailer
{

    private $_mailjet;

    private $_apikey;

    private $_secret;

    /**
     *  set your tracking event´s url
     *  bsp:
     *  [
     *      'bounce' => 'http://yoururl.com/tracking/bounce',
     *  ]
     */
    private $_tracking;

    private $_allowedTrackingEvents = [
        'sent',
        'open',
        'click',
        'bounce',
        'spam',
        'blocked',
        'unsub',
    ];

   /**
    * @var string message default class name.
    */
    public $messageClass = 'weluse\mailjet\Message';

   /**
    *  readonly
    * @var $_response Mailjet\Response
    */
    private $_response;

    public function init()
    {

        if (!$this->_apikey) {
            throw new InvalidConfigException(sprintf('"%s::apikey" cannot be null.', get_class($this)));
        }

        if (!$this->_secret) {
            throw new InvalidConfigException(sprintf('"%s::secret" cannot be null.', get_class($this)));
        }

        try {
            $this->createMailjet();
        } catch (\Exception $exc) {
            \Yii::error($exc->getMessage());
            throw new \Exception('an error occurred with your mailer. Please check the application logs.', 500);
        }
    }

    /**
     * Sets the API secret key for Mailjet
     *
     * @param string $secret
     * @throws InvalidConfigException
     */
     public function setSecret($secret)
     {

         if (!is_string($secret)) {
             throw new InvalidConfigException(sprintf('"%s::secret" should be a string, "%s" given.', get_class($this), gettype($apikey)));
         }
         $trimmedSecret = trim($secret);
         if (!strlen($trimmedSecret) > 0) {
             throw new InvalidConfigException(sprintf('"%s::secret" length should be greater than 0.', get_class($this)));
         }
         $this->_secret = $trimmedSecret;

     }

    /**
     * Sets the API key for Mailjet
     *
     * @param string $apikey the Mailjet API key
     * @throws InvalidConfigException
     */
    public function setApikey($apikey)
    {
        if (!is_string($apikey)) {
            throw new InvalidConfigException(sprintf('"%s::apikey" should be a string, "%s" given.', get_class($this), gettype($apikey)));
        }
        $trimmedApikey = trim($apikey);
        if (!strlen($trimmedApikey) > 0) {
            throw new InvalidConfigException(sprintf('"%s::apikey" length should be greater than 0.', get_class($this)));
        }
        $this->_apikey = $trimmedApikey;
    }

    /**
     *  Create the Mailjet Object
     */
    public function createMailjet()
    {

        $mj = new \Mailjet\Client($this->_apikey, $this->_secret);

        $this->_mailjet = $mj;
    }

    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @inheritdoc
     */
    protected function sendMessage($message)
    {

        $to = $cc = $bcc = [];

        foreach ($message->to as $email => $name) {
            $address = '<' . $email . '>';
            if (!empty($name)) {
                $address = '"' . $name . '" ' . $address;
            }
            $to[] = $address;
        }

        foreach ($message->cc as $email => $name) {
            $address = '<' . $email . '>';
            if (!empty($name)) {
                $address = '"' . $name . '" ' . $address;
            }
            $cc[] = $address;
        }


        foreach ($message->bcc as $email => $name) {
            $address = '<' . $email . '>';
            if (!empty($name)) {
                $address = '"' . $name . '" ' . $address;
            }
            $bcc[] = $address;
        }


        $body = [
            'Subject' => $message->subject,
            'Text-part' => $message->textBody,
            'Html-part' => $message->htmlBody,
            'To' => join(', ',$to),
        ];

        if ($cc) {
            $body['Cc'] = join(', ',$cc);
        }
        if ($bcc) {
            $body['Bcc'] = join(', ', $bcc);
        }

        if ($message->attachments) {
            $body['Attachments'] = $message->attachments;
        }
        if ($message->inlineAttachments) {
            $body['Inline_attachments'] = $message->inlineAttachments;
        }

        //Adds Reply-To to header
        if(!empty($message->replyTo)) {
            $body['Headers']['Reply-to'] = $message->replyTo;
        }

        $body = array_merge($message->from, $body);

        $response = $this->_mailjet->post(Resources::$Email, ['body' => $body]);
        $this->_response = $response;
        return $response->success();
    }

    public function setTracking($tracking)
    {

        if (is_array($tracking)) {

            $urlValidator = new UrlValidator;

            foreach ($tracking as $event => $url) {

                if (in_array($event, $this->_allowedTrackingEvents)) {

                    if (!$urlValidator->validate($url)) {
                        throw new InvalidConfigException(sprintf('"%s::%s" should be a url', get_class($this), $event));
                    }

                    $this->_tracking[$event] = $url;
                } else {
                    throw new InvalidConfigException(sprintf('the %s event is not supported', $event));
                }
            }

        } else {
            throw new InvalidConfigException('The trackingActions must be an array');
        }
    }

    public function activateAllTrackings()
    {
        foreach ($this->_tracking as $event => $url) {
            $this->activateTracking($event, $url);
        }

        return true;
    }

    public function activateTracking($event, $url)
    {
        $body = [
            'EventType' => $event,
            'Url' => $url,
        ];

        $response = $this->_mailjet->post(Resources::$Eventcallbackurl, ['body' => $body]);

        if (!$response->success()) {

            $eventCallbackurl = Resources::$Eventcallbackurl;
            $eventCallbackurl[1] = $event;

            $eventExist = $this->_mailjet->get($eventCallbackurl);

            $responseData = $eventExist->getData();

            /* check if is the tracking url the same  */
            if ($responseData[0]['Url'] != $url) {
                throw new UserException('You must clear your old tracking urls first: Yii::$app->mailer->clearAllTrackings(); or Yii::$app->mailer->clearTracking(\'' . $event . '\');');
            }
        }

        return true;
    }

    public function clearAllTrackings()
    {
        foreach ($this->_tracking as $event => $url) {
            $this->clearTracking($event);
        }
    }

    public function clearTracking($event)
    {
        if (!in_array($event, $this->_allowedTrackingEvents)) {
            throw new InvalidConfigException(sprintf('the %s event is not supported', $event));
        }

        $eventCallbackurl = Resources::$Eventcallbackurl;
        $eventCallbackurl[1] = $event;

        $response = $this->_mailjet->delete($eventCallbackurl);
    }

}
