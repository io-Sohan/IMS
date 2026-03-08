<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in</title>

    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Admin layout CSS -->
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 p-3">
<div class="card shadow-sm" style="max-width: 380px; width: 100%;">
    <div class="card-body p-4 p-lg-5">
        <h1 class="h5 fw-semibold mb-1">Log in</h1>
        <p class="text-muted small mb-4">Sign in to your account.</p>

        <form id="loginFrom">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" autocomplete="email" autofocus>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary w-100" id="loginBtn">Log in</button>
        </form>

    </div>
</div>

<!-- Bootstrap 5 JS (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Common JS (toast, etc.) -->
<script src="{{ asset('js/common.js') }}"></script>

<script>
    async function doLogin(){
        let emailValue = document.getElementById('email').value;
        let passwordValue = document.getElementById('password').value;
        let loginBtn = document.getElementById('loginBtn');

        let obj = {
            "email": emailValue,
            "password": passwordValue
        }

        let URL = '{{ url("/api/v1/login") }}';

        try{
            let response = await axios.post(URL,obj);

            if(response.status === 200){
                let data = response.data && response.data.data;
                if(data && data.token){
                    localStorage.setItem('token', data.token);
                    // Store token as an HttpOnly-safe cookie so the server middleware can read it
                    document.cookie = 'api_token=' + encodeURIComponent(data.token)
                        + '; path=/; max-age=' + (60 * 60 * 24 * 30) // 30 days
                        + '; SameSite=Lax';
                }
                window.location = '{{ route('dashboard') }}'
            }else{
                showErrorToast('Login Failed');
            }
        }catch (err){
            showErrorToast(getErrorMessage(err,'Login failed. Please check your credentials and try again.'));
        }
    }

    document.getElementById('loginFrom').addEventListener('submit', async function(e){
        e.preventDefault();
        await doLogin();
    })


</script>

</body>
</html>
