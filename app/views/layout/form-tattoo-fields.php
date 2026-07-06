<?php
/**
 * Randevu ve fiyat teklifi formlarının ortak dövme alanları.
 * $errors: alan bazlı hata mesajları, $withBudget: bütçe alanı gösterilsin mi
 */
$errors = $errors ?? [];
$withBudget = $withBudget ?? false;
$styles = rows_lang('SELECT title FROM services WHERE lang = ? AND status = 1 ORDER BY sort_order');
?>
<div class="form-group">
    <label for="f-name"><?= e(t('field.full_name')) ?> <span class="req">*</span></label>
    <input id="f-name" type="text" name="full_name" required maxlength="150" value="<?= e(old('full_name')) ?>">
    <?php if (isset($errors['full_name'])): ?><span class="field-error"><?= e($errors['full_name']) ?></span><?php endif; ?>
</div>
<div class="form-group">
    <label for="f-phone"><?= e(t('field.phone')) ?> <span class="req">*</span></label>
    <input id="f-phone" type="tel" name="phone" required maxlength="20" placeholder="05xx xxx xx xx" value="<?= e(old('phone')) ?>">
    <?php if (isset($errors['phone'])): ?><span class="field-error"><?= e($errors['phone']) ?></span><?php endif; ?>
</div>
<div class="form-group">
    <label for="f-email"><?= e(t('field.email')) ?></label>
    <input id="f-email" type="email" name="email" maxlength="190" value="<?= e(old('email')) ?>">
    <?php if (isset($errors['email'])): ?><span class="field-error"><?= e($errors['email']) ?></span><?php endif; ?>
</div>
<div class="form-group">
    <label for="f-area"><?= e(t('field.tattoo_area')) ?> <span class="req">*</span></label>
    <input id="f-area" type="text" name="tattoo_area" required maxlength="150" placeholder="<?= e(t('placeholder.tattoo_area')) ?>" value="<?= e(old('tattoo_area')) ?>">
    <?php if (isset($errors['tattoo_area'])): ?><span class="field-error"><?= e($errors['tattoo_area']) ?></span><?php endif; ?>
</div>
<div class="form-group">
    <label for="f-size"><?= e(t('field.tattoo_size')) ?></label>
    <input id="f-size" type="text" name="tattoo_size" maxlength="100" placeholder="<?= e(t('placeholder.tattoo_size')) ?>" value="<?= e(old('tattoo_size')) ?>">
</div>
<div class="form-group">
    <label for="f-style"><?= e(t('field.style')) ?></label>
    <select id="f-style" name="style">
        <option value=""><?= e(t('option.select')) ?></option>
        <?php foreach ($styles as $st): ?>
        <option value="<?= e($st['title']) ?>" <?= old('style') === $st['title'] ? 'selected' : '' ?>><?= e($st['title']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="form-group full">
    <span style="font-weight:600;font-size:.88rem"><?= e(t('field.color_type')) ?></span>
    <div class="choice-row">
        <label><input type="radio" name="color_type" value="blackgray" <?= old('color_type', 'blackgray') === 'blackgray' ? 'checked' : '' ?>> <?= e(t('option.blackgray')) ?></label>
        <label><input type="radio" name="color_type" value="color" <?= old('color_type') === 'color' ? 'checked' : '' ?>> <?= e(t('option.color')) ?></label>
        <label><input type="radio" name="color_type" value="undecided" <?= old('color_type') === 'undecided' ? 'checked' : '' ?>> <?= e(t('option.undecided')) ?></label>
    </div>
</div>
<?php if ($withBudget): ?>
<div class="form-group">
    <label for="f-budget"><?= e(t('field.budget_range')) ?></label>
    <input id="f-budget" type="text" name="budget_range" maxlength="100" placeholder="<?= e(t('placeholder.budget')) ?>" value="<?= e(old('budget_range')) ?>">
</div>
<?php endif; ?>
<?php
// Randevu formunda tarih/saat zorunlu; teklif formunda ($withBudget) opsiyonel.
$dtRequired = !$withBudget;
$timeSlots = [];
for ($h = 9; $h <= 23; $h++) {
    $timeSlots[] = sprintf('%02d:00', $h);
    if ($h < 23) {
        $timeSlots[] = sprintf('%02d:30', $h);
    }
}
?>
<div class="form-group">
    <label for="f-date"><?= e(t('field.appointment_date')) ?><?= $dtRequired ? ' <span class="req">*</span>' : '' ?></label>
    <input id="f-date" type="date" name="appointment_date" <?= $dtRequired ? 'required' : '' ?>
           min="<?= e(date('Y-m-d')) ?>" max="<?= e(date('Y-m-d', strtotime('+1 year'))) ?>"
           value="<?= e(old('appointment_date')) ?>" data-appt-date>
    <?php if (isset($errors['appointment_date'])): ?><span class="field-error"><?= e($errors['appointment_date']) ?></span><?php endif; ?>
</div>
<div class="form-group">
    <label for="f-time"><?= e(t('field.appointment_time')) ?><?= $dtRequired ? ' <span class="req">*</span>' : '' ?></label>
    <select id="f-time" name="appointment_time" <?= $dtRequired ? 'required' : '' ?> data-appt-time>
        <option value=""><?= e(t('option.select')) ?></option>
        <?php foreach ($timeSlots as $slot): ?>
        <option value="<?= $slot ?>" <?= old('appointment_time') === $slot ? 'selected' : '' ?>><?= $slot ?></option>
        <?php endforeach; ?>
    </select>
    <?php if (isset($errors['appointment_time'])): ?><span class="field-error"><?= e($errors['appointment_time']) ?></span><?php endif; ?>
    <span class="field-hint"><?= e(t('form.datetime_hint')) ?></span>
</div>
<div class="form-group full">
    <span style="font-weight:600;font-size:.88rem"><?= e(t('field.has_previous_tattoo')) ?></span>
    <div class="choice-row">
        <label><input type="radio" name="has_previous_tattoo" value="0" <?= old('has_previous_tattoo', '0') === '0' ? 'checked' : '' ?>> <?= e(t('option.no')) ?></label>
        <label><input type="radio" name="has_previous_tattoo" value="1" <?= old('has_previous_tattoo') === '1' ? 'checked' : '' ?>> <?= e(t('option.yes')) ?></label>
    </div>
</div>
<div class="form-group full">
    <label for="f-ref"><?= e(t('field.reference_image')) ?></label>
    <input id="f-ref" type="file" name="reference_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
    <span class="field-hint"><?= e(t('form.reference_hint', ['max' => (int) (UPLOAD_MAX_BYTES / 1048576)])) ?></span>
    <?php if (isset($errors['reference_image'])): ?><span class="field-error"><?= e($errors['reference_image']) ?></span><?php endif; ?>
</div>
<div class="form-group full">
    <label for="f-desc"><?= e(t('field.description')) ?></label>
    <textarea id="f-desc" name="description" maxlength="3000" placeholder="<?= e(t('placeholder.description')) ?>"><?= e(old('description')) ?></textarea>
</div>
