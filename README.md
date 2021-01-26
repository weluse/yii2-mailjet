# Mailjet Client

## Create Mailjet Account

https://goo.gl/YNWTwd

## Install

```
composer require jafarili/yii2-mailjet
```

or add it to your composer.json in the require section

```
"jafarili/yii2-mailjet": "*",
```

## Setup

add/replace this in your config under the components key.

```
'components' => [
  'mailer' => [
    'class' => 'jafarili\mailjet\Mailer',
    'apikey' => 'yourApiKey',
    'secret' => 'yourSecret',
  ],
],
```

Or set a MailJet client instead:

```
'components' => [
  'mailer' => [
    'class' => 'jafarili\mailjet\Mailer',
    'mailjet' => new \Mailjet\Client('yourApiKey', 'yourSecret'),
  ],
],
```

## Example

```
Yii::$app->mailer->compose('signup', ['user' => $user])
->setTo($user->email)
->setFrom([Yii::$app->params['noReplyMailAddress'] => Yii::$app->name])
->setSubject('Signup success')
->send();
```

## Attachment example

```
// Mail with attachment from string via Message::attachContent()
Yii::$app->mailer->compose('view-name')
->setSubject('Mail with attachment from content')
->attachContent("This is the attachment content", ['fileName' => 'attachment.txt', 'contentType' => 'text/plain'])
->setTo('info@example.com')
->send();

// Mail with attachment from file via Message::attach()
$filePath = ... // a file path here;
Yii::$app->mailer->compose('view-name')
->setSubject('Mail with attachment from content')
->attach($filePath)
->setTo('info@example.com')
->send();
```

## Setup Event Tracking

Write the tracking item to the mailer config.

```
'components' => [
  'mailer' => [
    'class' => 'jafarili\mailjet\Mailer',
    'apikey' => 'yourApiKey',
    'secret' => 'yourSecret',
    'tracking' => [
      'bounce' => 'http://yoururl.com/tracking?event=bounce',
    ],
  ],
],
```

To activate this url you must run this command at one time.

```
Yii::$app->mailer->activateTracking();
```
