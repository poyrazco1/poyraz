<?php
/**
 * Fiyat teklifi formu POST işleyici (popup formu da buraya gönderir).
 */

Csrf::check();

$fromPopup = post('form_source') === 'popup';
$backUrl = url('fiyat-teklifi-al');

if (is_spam_submission()) {
    flash_set('success', t('form.success_quote'));
    redirect($backUrl);
}

$data = [
    'full_name'    => post('full_name'),
    'phone'        => post('phone'),
    'email'        => post('email'),
    'tattoo_area'  => post('tattoo_area'),
    'tattoo_size'  => post('tattoo_size'),
    'color_type'   => post('color_type'),
    'style'        => post('style'),
    'description'  => post('description'),
    'budget_range' => post('budget_range'),
];

// Popup formu yalnızca ad + telefon + stil gönderir
$rules = $fromPopup
    ? ['full_name' => 'required|min:3|max:150', 'phone' => 'required|phone', 'style' => 'max:150']
    : [
        'full_name'    => 'required|min:3|max:150',
        'phone'        => 'required|phone',
        'email'        => 'email|max:190',
        'tattoo_area'  => 'required|max:150',
        'tattoo_size'  => 'max:100',
        'style'        => 'max:150',
        'color_type'   => 'in:color,blackgray,undecided',
        'description'  => 'max:3000',
        'budget_range' => 'max:100',
    ];

$errors = validate($data, $rules);

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

if ($fromPopup && $data['description'] === '') {
    $data['description'] = 'Hızlı teklif popup formundan gönderildi.';
}
$data['reference_image'] = $referencePath;
$data['status'] = 'new';
Database::insert('quote_requests', $data);

old_clear();
flash_set('success', t('form.success_quote'));
redirect($backUrl);
