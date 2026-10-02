<?php use yii\helpers\Url; ?>
<form id="lead-form"
      hx-post="<?= Url::to(['/lead/create/' . $landingId]) ?>"
      hx-target="#lead-form-wrapper"
      hx-swap="innerHTML">

    <input type="hidden" name="utm_source"   id="utm_source">
    <input type="hidden" name="utm_medium"   id="utm_medium">
    <input type="hidden" name="utm_campaign" id="utm_campaign">

    <div class="field-group">
        <label>Ваше имя *</label>
        <input type="text" name="name" class="input" required placeholder="Иван">
    </div>

    <div class="field-group">
        <label>Телефон *</label>
        <input type="tel" name="phone" class="input" required placeholder="+7 900 000-00-00">
    </div>

    <div class="field-group">
        <label>Email</label>
        <input type="email" name="email" class="input" placeholder="необязательно">
    </div>

    <div class="field-group">
        <label>Сообщение</label>
        <textarea name="message" class="input" rows="2" placeholder="Что вас интересует? (необязательно)"></textarea>
    </div>

    <button type="submit" class="btn-primary w-full py-3">Отправить заявку</button>

    <p class="text-xs text-base-400 text-center mt-3">
        Нажимая кнопку, вы соглашаетесь на обработку персональных данных
    </p>
</form>

<script>
(function() {
    const params = new URLSearchParams(window.location.search);
    ['utm_source', 'utm_medium', 'utm_campaign'].forEach(key => {
        const value = params.get(key);
        if (value) {
            const input = document.getElementById(key);
            if (input) input.value = value;
        }
    });
})();
</script>