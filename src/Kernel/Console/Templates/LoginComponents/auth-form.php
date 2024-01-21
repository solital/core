{% extend('auth.header') %}

<main class="form-signin">
    <form action="{{ url('auth.post'); }}" method="POST">
        <h1 class="mb-3 fw-normal">{{ $title }}</h1>

        {{ csrf_token() }}

        {% if ($msg) : %}
            <div class="alert alert-info" role="alert">
                {{ $msg }}
            </div>
        {% endif; %}

        <div class="form-floating">
            <input type="email" class="form-control" id="floatingInput" name="inputEmail" placeholder="name@example.com">
            <label for="floatingInput">Email address</label>
        </div>

        <div class="form-floating">
            <input type="password" class="form-control" id="floatingPassword" name="inputPassword" placeholder="Password">
            <label for="floatingPassword">Password</label>
        </div>

        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="inputRemember" name="inputRemember" value="true">
            <label class="form-check-label" for="inputRemember">Remember?</label>
        </div>

        <button class="w-100 btn btn-lg btn-primary mt-3" type="submit">
            <span class="material-icons">send</span> Login
        </button>

        <div class="form-floating mt-4">
            {% if (url("forgot")) : %}
                <a href="{{ url('forgot') }}">Forgot password</a>
            {% endif; %}
        </div>

        <p class="mt-5 mb-3 text-muted">&copy; 2020 - {{ date('Y') }}</p>
    </form>
</main>

{% extend('auth.footer') %}