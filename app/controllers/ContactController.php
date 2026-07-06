<?php
/**
 * İletişim formu POST işleyici.
 */

Csrf::check();

$backUrl = url('iletisim');

if (is_spam_submission()) {
    flash_set('success', t('form.success_contact'));
    redirect($backUrl);
}

$data = [
    'full_name' => post('full_name'),
    'phone'     => post('phone'),
    'email'     => post('email'),
    'subject'   => post('subject'),
    'message'   => post('message'),
];

$errors = validate($data, [
    'full_name' => 'required|min:3|max:150',
    'phone'     => 'required|phone',
    'email'     => 'email|max:190',
    'subject'   => 'max:190',
    'message'   => 'required|min:5|max:3000',
]);

if ($errors) {
    $_SESSION['form_errors'] = $errors;
    old_set($_POST);
    flash_set('error', t('form.error_general'));
    redirect($backUrl);
}

$data['status'] = 'new';
Database::insert('contact_messages', $data);

old_clear();
flash_set('success', t('form.success_contact'));
redirect($backUrl);
