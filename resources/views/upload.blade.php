<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WAF za File Upload napade - demo</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            max-width: 760px; margin: 40px auto; padding: 0 16px;
            background: #0f172a; color: #e2e8f0;
        }
        h1 { font-size: 1.4rem; }
        .card {
            background: #1e293b; border: 1px solid #334155;
            border-radius: 12px; padding: 20px; margin-top: 16px;
        }
        label { display: block; margin: 12px 0 6px; font-weight: 600; }
        input[type=file], select {
            width: 100%; padding: 10px; border-radius: 8px;
            border: 1px solid #475569; background: #0f172a; color: #e2e8f0;
        }
        .targets { display: flex; gap: 12px; margin-top: 8px; }
        .targets label {
            flex: 1; display: flex; align-items: center; gap: 8px;
            background: #0f172a; border: 1px solid #475569; border-radius: 8px;
            padding: 10px; margin: 0; cursor: pointer; font-weight: 500;
        }
        button {
            margin-top: 16px; width: 100%; padding: 12px;
            border: 0; border-radius: 8px; background: #3b82f6; color: #fff;
            font-size: 1rem; font-weight: 600; cursor: pointer;
        }
        button:disabled { opacity: .6; cursor: not-allowed; }
        #result {
            margin-top: 16px; padding: 14px; border-radius: 8px;
            white-space: pre-wrap; word-break: break-all; display: none;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: .85rem;
        }
        #result.ok    { display: block; background: #064e3b; border: 1px solid #10b981; }
        #result.block { display: block; background: #7f1d1d; border: 1px solid #ef4444; }
        #result a { color: #93c5fd; }
        .hint { color: #94a3b8; font-size: .8rem; margin-top: 6px; }
    </style>
</head>
<body>
    <h1>WAF za File Upload napade — demo upload</h1>
    <p class="hint">Izaberi metu: <b>ranjiva</b> ruta nema zastitu, <b>zasticena</b> ruta
        ide kroz WAF middleware.</p>

    <div class="card">
        <form id="uploadForm">
            <label for="file">Fajl za upload</label>
            <input type="file" id="file" name="file" required>

            <label>Meta</label>
            <div class="targets">
                <label><input type="radio" name="target" value="/vulnerable/upload" checked> Ranjiva ruta</label>
                <label><input type="radio" name="target" value="/secure/upload"> Zasticena ruta (WAF)</label>
            </div>

            <button type="submit" id="submitBtn">Posalji</button>
        </form>
        <div id="result"></div>
    </div>

    <script>
        const form = document.getElementById('uploadForm');
        const result = document.getElementById('result');
        const btn = document.getElementById('submitBtn');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const fileInput = document.getElementById('file');
            if (!fileInput.files.length) {
                result.className = 'block';
                result.style.display = 'block';
                result.textContent = 'Izaberi fajl pre slanja.';
                return;
            }

            const target = document.querySelector('input[name="target"]:checked').value;
            const data = new FormData();
            data.append('file', fileInput.files[0]);

            btn.disabled = true;
            result.className = '';
            result.style.display = 'block';
            result.textContent = 'Slanje...';

            try {
                const res = await fetch(target, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: data,
                });
                const json = await res.json();
                render(res.status, json);
            } catch (err) {
                result.className = 'block';
                result.textContent = 'Greska u komunikaciji sa serverom: ' + err;
            } finally {
                btn.disabled = false;
            }
        });

        function render(httpStatus, json) {
            // WAF blokada vraca status 403 / 422; uspesan upload 200.
            const blocked = httpStatus >= 400;
            result.className = blocked ? 'block' : 'ok';

            let text = 'HTTP ' + httpStatus + '\n';
            text += JSON.stringify(json, null, 2);
            result.textContent = text;

            if (!blocked && json.public_url) {
                const a = document.createElement('a');
                a.href = json.public_url;
                a.textContent = '\n\nOtvori sacuvani fajl: ' + json.public_url;
                a.target = '_blank';
                result.appendChild(a);
            }
        }
    </script>
</body>
</html>
