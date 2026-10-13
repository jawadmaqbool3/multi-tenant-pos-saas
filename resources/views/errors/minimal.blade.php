<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title') | {{ config('app.name', 'POS') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <style>
            :root {
                --primary: #4f46e5;
                --text: #0f172a;
                --muted: #64748b;
                --surface: #ffffff;
                --border: #e2e8f0;
            }

            * {
                box-sizing: border-box;
            }

            html,
            body {
                height: 100%;
            }

            body {
                margin: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                padding: 1.5rem;
                background: radial-gradient(1200px 600px at 50% -10%, #eef2ff 0%, #f8fafc 55%, #f1f5f9 100%);
                color: var(--text);
                font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
                -webkit-font-smoothing: antialiased;
            }

            .error-card {
                width: 100%;
                max-width: 30rem;
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 1rem;
                box-shadow: 0 10px 30px -12px rgba(15, 23, 42, 0.2);
                padding: 2.5rem;
                text-align: center;
            }

            .brand {
                font-size: 0.8rem;
                font-weight: 600;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: var(--muted);
            }

            .code {
                margin: 1rem 0 0.5rem;
                font-size: 3.5rem;
                font-weight: 700;
                line-height: 1;
                background: linear-gradient(135deg, var(--primary), #312e81);
                -webkit-background-clip: text;
                background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .message {
                font-size: 1.125rem;
                font-weight: 600;
                color: var(--text);
            }

            .hint {
                margin-top: 0.5rem;
                font-size: 0.9rem;
                color: var(--muted);
            }

            .actions {
                margin-top: 1.75rem;
            }

            .btn {
                display: inline-block;
                padding: 0.7rem 1.4rem;
                border-radius: 0.625rem;
                background: var(--primary);
                color: #fff;
                font-weight: 600;
                font-size: 0.95rem;
                text-decoration: none;
                transition: background-color 0.15s ease;
            }

            .btn:hover {
                background: #4338ca;
            }

            @media (prefers-color-scheme: dark) {
                :root {
                    --text: #f1f5f9;
                    --muted: #94a3b8;
                    --surface: #1e293b;
                    --border: #334155;
                }

                body {
                    background: radial-gradient(1200px 600px at 50% -10%, #1e1b4b 0%, #0f172a 55%, #0b1120 100%);
                }
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div class="brand">{{ config('app.name', 'POS') }}</div>
            <div class="code">@yield('code')</div>
            <div class="message">@yield('message')</div>
            <div class="hint">Sorry, something went wrong. Please try again or head back home.</div>
            <div class="actions">
                <a class="btn" href="{{ url('/') }}">Go home</a>
            </div>
        </div>
    </body>
</html>
