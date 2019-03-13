<?php
$this->layout = 'panel';

$user = \MapasCulturais\App::i()->user;

if(false) $fb = new \Facebook\Facebook();

eval(\psy\sh());

?>
<div class="panel-list panel-main-content">
<?php if($user->facebookAccessToken): ?>
<?php else: ?>
<?php endif; ?>
<a href="<?php echo $login_url ?>">link</a>
</div>