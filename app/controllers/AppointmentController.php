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
]);

// Randevu tarihi + saati: format, çalışma saati (09:00-23:00) ve geçmiş kontrolü
$apptDate = post('appointment_date');
$apptTime = post('appointment_time');
$preferredDatetime = null;

if ($apptDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $apptDate)) {
    $errors['appointment_date'] = t('validation.required', ['field' => t('field.appointment_date')]);
}
if ($apptTime === '' || !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $apptTime)) {
    $errors['appointment_time'] = t('validation.required', ['field' => t('field.appointment_time')]);
}
if (!isset($errors['appointment_date'], $errors['appointment_time'])
    && $apptDate !== '' && $apptTime !== ''
    && preg_match('/^\d{4}-\d{2}-\d{2}$/', $apptDate)
    && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $apptTime)) {

    [$y, $m, $d] = array_map('intval', explode('-', $apptDate));
    [$hh, $mm] = array_map('intval', explode(':', $apptTime));

    if (!checkdate($m, $d, $y)) {
        $errors['appointment_date'] = t('validation.invalid', ['field' => t('field.appointment_date')]);
    } elseif ($hh < 9 || $hh > 23 || ($hh === 23 && $mm > 0)) {
        $errors['appointment_time'] = t('validation.time_range');
    } else {
        $ts = mktime($hh, $mm, 0, $m, $d, $y);
        if ($ts === false || $ts < time()) {
            $errors['appointment_date'] = t('validation.past_datetime');
        } else {
            $preferredDatetime = date('Y-m-d H:i:s', $ts);
        }
    }
}

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

$data['preferred_datetime'] = $preferredDatetime;
$data['reference_image'] = $referencePath;
$data['status'] = 'new';
Database::insert('appointments', $data);

old_clear();
flash_set('success', t('form.success_appointment'));
redirect($backUrl);
