<?php

namespace weluse\mailjet;

use Mailjet\Resources;
use yii\mail\BaseMessage;
use yii\base\Exception;

/**
 * Contains the Message class
 *
 * @package weluse/mailjet
 */
class Message extends BaseMessage {

    private $_charset;

    private $_from;

    private $_to;

    private $_replyTo;

    private $_cc;

    private $_bcc;

    private $_subject;

    private $_textBody;

    private $_htmlBody;

    private $_attachments;

    private $_inline_attachments;

    /**
     * @inheritdoc
     */
    public function getCharset() {
        return $this->_charset;
    }

    /**
     * @inheritdoc
     */
    public function setCharset($charset) {
        $this->_charset = $charset;
    }

    /**
     * @inheritdoc
     */
    public function getFrom() {
        return $this->_from;
    }

    /**
     * @inheritdoc
     */
    public function setFrom($from) {

        if (is_array($from)) {
            $this->_from = [
                'FromEmail' => key($from),
                'FromName' => array_shift($from),
            ];
        } else {
            $this->_from['FromEmail'] = $from;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTo() {
        return $this->_to;
    }

    /**
     * @inheritdoc
     */
    public function setTo($to) {
        if (!is_array($to)){
            $to = [$to => ''];
        }
        $this->_to = $to;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReplyTo() {
        return $this->_replyTo;
    }

    /**
     * @inheritdoc
     */
    public function setReplyTo($replyTo) {
        $this->_replyTo = $replyTo;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCc() {
        return $this->_cc;
    }

    /**
     * @inheritdoc
     */
    public function setCc($cc) {
        if (!is_array($cc)){
            $cc = [$cc => ''];
        }
        $this->_cc = $cc;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBcc() {
        return $this->_bcc;
    }

    /**
     * @inheritdoc
     */
    public function setBcc($bcc) {
        if (!is_array($bcc)){
            $bcc = [$bcc => ''];
        }
        $this->_bcc = $bcc;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubject() {
        return $this->_subject;
    }

    /**
     * @inheritdoc
     */
    public function setSubject($subject) {
        $this->_subject = $subject;
        return $this;
    }

    /**
     * return the plain text for the mail
     */
     public function getTextBody() {
         return $this->_textBody;
     }

    /**
    * @inheritdoc
    */
    public function setTextBody($text) {
        $this->_textBody = $text;
        return $this;
    }

    /**
    * return the html text for the mail
    */
    public function getHtmlBody() {
        return $this->_htmlBody;
    }

    /**
    * @inheritdoc
    */
    public function setHtmlBody($html) {
        $this->_htmlBody = $html;
        return $this;
    }

    /**
    * @inheritdoc
    */
    public function attach($fileName, array $options = []) {
        $attachment = [
            'Content-type' => isset($options['Content-type']) ? $options['Content-type'] : \yii\helpers\FileHelper::getMimeType($fileName),
            'Filename' => isset($options['fileName']) ? $options['fileName'] : basename($fileName),
            'content' => base64_encode(file_get_contents($fileName)),
        ];
        $this->_attachments[] = $attachment;
        return $this;
    }

    /**
    * @inheritdoc
    */
    public function attachContent($content, array $options = []) {
        $attachment = [
            'Content-type' => isset($options['Content-type']) ? $options['Content-type'] : 'text/plain',
            'Filename' => isset($options['fileName']) ? $options['fileName'] : 'attachment.txt',
            'content' => base64_encode($content),
        ];
        $this->_attachments[] = $attachment;
        return $this;
    }

    /**
    * @inheritdoc
    */
    public function embed($fileName, array $options = []) {
        $attachment = [
            'Content-type' => isset($options['Content-type']) ? $options['Content-type'] : \yii\helpers\FileHelper::getMimeType($fileName),
            'Filename' => isset($options['fileName']) ? $options['fileName'] : basename($fileName),
            'content' => base64_encode(file_get_contents($fileName)),
        ];
        $this->_inline_attachments[] = $attachment;
        return $attachment['Filename'];
    }

    /**
    * @inheritdoc
    */
    public function embedContent($content, array $options = []) {
        $attachment = [
            'Content-type' => isset($options['Content-type']) ? $options['Content-type'] : 'text/plain',
            'Filename' => isset($options['fileName']) ? $options['fileName'] : 'attachment.txt',
            'content' => base64_encode($content),
        ];
        $this->_inline_attachments[] = $attachment;
        return $attachment['Filename'];
    }

    /**
    * @inheritdoc
    */
    public function toString() {
        return implode(',', $this->getTo()) . "\n"
            . $this->getSubject() . "\n"
            . $this->getTextBody();
    }

    /**
     * @return mixed
     */
    public function getAttachments()
    {
        return $this->_attachments;
    }

    /**
     * @return mixed
     */
    public function getInlineAttachments()
    {
        return $this->_inline_attachments;
    }
}
