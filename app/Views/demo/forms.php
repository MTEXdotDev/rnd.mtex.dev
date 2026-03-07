<h1 class="demo-heading">Form + Validation</h1>
<p class="demo-subheading">
    Submit the form below. PHP-Zero validates via <code>Validator::make()</code>, re-populates
    fields with <code>old()</code> on failure, and shows per-field errors from the session flash.
</p>

<?php if ($success ?? false): ?>
    <div class="alert alert-success" style="margin-bottom:1.5rem">
        <div style="display:flex;align-items:center;gap:.5rem">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            Form submitted successfully! All fields passed validation.
        </div>
    </div>
    <div class="demo-section">
        <div class="demo-section__title">Validated data</div>
        <div class="demo-panel">
            <div class="demo-panel__body">
                <table class="kv-table">
                    <?php foreach ($validated as $key => $value): ?>
                        <tr>
                            <td><?= e($key) ?></td>
                            <td><?= e(is_string($value) ? $value : json_encode($value)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="demo-section">
    <div class="demo-section__title">Registration form</div>
    <div class="demo-panel">
        <div class="demo-panel__header">
            <span class="method method-post">POST</span>
            <span>/demo/forms</span>
        </div>
        <div class="demo-panel__body">
            <form method="POST" action="/demo/forms" class="demo-form" style="display:flex;flex-direction:column;gap:1rem">
                <?= csrf_field() ?>

                <div class="form-row">
                    <div class="form-group" style="margin:0">
                        <label class="form-label" for="name">Name <span style="color:#ef4444">*</span></label>
                        <input class="form-input <?= errors('name') ? 'input-error' : '' ?>"
                               type="text" id="name" name="name"
                               value="<?= attr(old('name')) ?>"
                               placeholder="Alice Smith">
                        <?php if ($e = errors('name')): ?>
                            <div class="field-error"><?= e($e) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group" style="margin:0">
                        <label class="form-label" for="username">Username <span style="color:#ef4444">*</span></label>
                        <input class="form-input <?= errors('username') ? 'input-error' : '' ?>"
                               type="text" id="username" name="username"
                               value="<?= attr(old('username')) ?>"
                               placeholder="alice_dev">
                        <?php if ($e = errors('username')): ?>
                            <div class="field-error"><?= e($e) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group" style="margin:0">
                    <label class="form-label" for="email">Email <span style="color:#ef4444">*</span></label>
                    <input class="form-input <?= errors('email') ? 'input-error' : '' ?>"
                           type="email" id="email" name="email"
                           value="<?= attr(old('email')) ?>"
                           placeholder="alice@example.com">
                    <?php if ($e = errors('email')): ?>
                        <div class="field-error"><?= e($e) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group" style="margin:0">
                        <label class="form-label" for="age">Age <span style="color:#94a3b8;font-weight:400">(optional, 13–120)</span></label>
                        <input class="form-input <?= errors('age') ? 'input-error' : '' ?>"
                               type="number" id="age" name="age"
                               value="<?= attr(old('age')) ?>"
                               placeholder="25">
                        <?php if ($e = errors('age')): ?>
                            <div class="field-error"><?= e($e) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group" style="margin:0">
                        <label class="form-label" for="role">Role <span style="color:#ef4444">*</span></label>
                        <select class="form-input <?= errors('role') ? 'input-error' : '' ?>" id="role" name="role">
                            <option value="">— select —</option>
                            <?php foreach (['admin', 'editor', 'viewer'] as $r): ?>
                                <option value="<?= attr($r) ?>" <?= old('role') === $r ? 'selected' : '' ?>>
                                    <?= e(ucfirst($r)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($e = errors('role')): ?>
                            <div class="field-error"><?= e($e) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group" style="margin:0">
                    <label class="form-label" for="bio">Bio <span style="color:#94a3b8;font-weight:400">(optional, max 300 chars)</span></label>
                    <textarea class="form-input <?= errors('bio') ? 'input-error' : '' ?>"
                              id="bio" name="bio" rows="3"
                              placeholder="Tell us about yourself..."
                              style="resize:vertical"><?= e(old('bio')) ?></textarea>
                    <?php if ($e = errors('bio')): ?>
                        <div class="field-error"><?= e($e) ?></div>
                    <?php endif; ?>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                        Submit form
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="demo-section">
    <div class="demo-section__title">Validation rules applied</div>
    <div class="code-block"><span class="t-var">$data</span> = <span class="t-fn">validate</span>(<span class="t-cls">Request</span>::<span class="t-fn">all</span>(), [
    <span class="t-str">'name'</span>     => <span class="t-str">'required|min_length:2|max_length:80'</span>,
    <span class="t-str">'username'</span>  => <span class="t-str">'required|min_length:3|max_length:30|alpha_dash'</span>,
    <span class="t-str">'email'</span>    => <span class="t-str">'required|email'</span>,
    <span class="t-str">'age'</span>      => <span class="t-str">'nullable|numeric|min:13|max:120'</span>,
    <span class="t-str">'role'</span>     => <span class="t-str">'required|in:admin,editor,viewer'</span>,
    <span class="t-str">'bio'</span>      => <span class="t-str">'nullable|max_length:300'</span>,
]);</div>
</div>
