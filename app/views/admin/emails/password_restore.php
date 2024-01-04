<!doctype html>
<html>
    <head>
        <meta name="viewport" content="width=device-width" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?= t('restore_your_password') ?></title>
    </head>
    <body style="display: flex; flex-direction: column; background-color: #f6f8fa; font-size: 14px; font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif; margin: 20px;">
        <div style="display: flex; flex-direction: column; background-color: white; border-radius: 8px; padding: 20px; box-shadow: 0px 0px 8px 0px #e7e7e7;">
        <p style="margin: 0;"><?= t('someone_requested_password_restore') ?></p>
        <a style="background-color: #25292e; align-self: flex-start; color: white; text-decoration: none; padding: 10px 20px; border-radius: 6px; margin-top: 20px;" href="<?= e(url('admin/new_password?hash=' . $hash)) ?>" target="_blank"><?= t('click_to_restore_password') ?></a>
        </div>
        <a href="<?= e(url()) ?>" style="align-self: center; margin-top: 20px;"><?= e(url()) ?></a>
    </body>
</html>
