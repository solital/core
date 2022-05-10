{% extend('auth.header') %}

<main class="form-signin">
    <form action="{{ url('change.post', ['hash' => $hash]); }}" method="POST">
        <h1 class="mb-3 fw-normal">{{ $title }}</h1>

        {{ csrf_token() }}

        {% if ($msg) : %}
            <div class="alert alert-info" role="alert">
                {{ $msg }}
            </div>
        {% endif; %}

        <input type="hidden" name="inputEmail" value="{{ $email; }}">
        <input type="hidden" name="hash" value="{{ $hash; }}">

        <div class="form-floating">
            <input type="password" class="form-control" id="floatingPassword" name="inputPass" placeholder="New password">
            <label for="floatingPassword">New password</label>
        </div>

        <div class="form-floating">
            <input type="password" class="form-control" id="floatingNewPassword" name="inputConfPass" placeholder="Repeat new password">
            <label for="floatingNewPassword">Repeat new password</label>
        </div>

        <button class="w-100 btn btn-lg btn-primary mt-3" type="submit">Change</button>

        <p class="mt-5 mb-3 text-muted">&copy; 2020 - {{ date('Y') }}</p>
    </form>
</main>

{% extend('auth.footer') %}