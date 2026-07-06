<?php
/**
 * Randevu formu POST işleyici.
 * CSRF + honeypot + server-side validation + güvenli upload + PRG.
 */

Csrf::check();

$backUrl = url('randevu-al');

if (is_spam_submission()) {
    // Bot: sessizce başarı gibi davran
    flash_set('success', t('form.success_appointment'));
    redirect($backUrl);
}

$data = [
    'full_name'          => post('full_name'),
    'phone'              => post('phone'),
    'email'              => post('email'),
    'tattoo_area'        => post('tattoo_area'),
    'tattoo_size'        => post('tattoo_size'),
    'color_type'         => post('color_type'),
    'style'              => post('style'),
    'description'        => post('description'),
    'preferred_datetime' => post('preferred_datetime'),
    'has_previous_tattoo' => post('has_previous_tattoo') === '1' ? 1 : 0,
];

$errors = validate($data, [
    'full_name'   => 'required|min:3|max:150',
    'phone'       => 'required|phone',
    'email'       => 'email|max:190',
    'tattoo_area' => 'required|max:150',
    'tattoo_size' => 'max:100',
    'style'       => 'max:150',
    'color_type'  => 'in:color,blackgray,undecided',
    'description' => 'max:3000',
    'preferred_datetime' => 'max:190',
]);

// Referans görsel (opsiyonel)
$referencePath = null;
if (empty($errors) && isset($_FILES['reference_image'])) {
    $up = upload_image($_FILES['reference_image'], 'references');
    if (!$up['ok']) {
        $errors['reference_image'] = $up['error'];
    } else {
        $referencePath = $up['path'];
    }
}

if ($errors) {
    $_SESSION['form_errors'] = $errors;
    old_set($_POST);
    flash_set('error', t('form.error_general'));
    redirect($backUrl);
}

$data['reference_image'] = $referencePath;
$data['status'] = 'new';
Database::insert('appointments', $data);

old_clear();
flash_set('success', t('form.success_appointment'));
redirect($backUrl);
