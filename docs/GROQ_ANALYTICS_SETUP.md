# Enable Groq online AI for analytics

Groq is now integrated with **CHO → Analytics → Ask about this report**. The model runs online, so you do not need to download a model or run Ollama. Internet access is required. Groq has a free plan with request/token quotas; it is not unlimited free usage.

## 1. Create your account and API key

1. Sign in or create an account at [Groq Console](https://console.groq.com/).
2. Open [API Keys](https://console.groq.com/keys), choose **Create API Key**, and give it a name such as `ReproCare Analytics`.
3. Copy the key directly into your local `.env` in the next step. Do not paste it into chat, frontend JavaScript, screenshots, or source control. The assistant cannot create an account or retrieve the key for you.
4. For a free prototype, stay on the free plan. Check the account's limits before any demonstration; input and output token limits can be reached before the request limit.
5. Under **Settings → Data Controls**, enable **Zero Data Retention** for inference. This is a provider-side setting; the PHP app cannot enable or verify it for you.

Provider instructions: [Groq quickstart](https://console.groq.com/docs/quickstart), [free-plan limits](https://console.groq.com/docs/rate-limits), [data controls](https://console.groq.com/docs/your-data).

## 2. Add the key on the Laravel server

Open:

```text
C:\xampp\htdocs\CapstoneCurrent\CapstoneProject\reprocare\.env
```

Find these settings, replacing existing values instead of adding duplicate lines:

```dotenv
ANALYTICS_AI_PROVIDER=groq
GROQ_API_KEY=paste_your_key_here
GROQ_MODEL=qwen/qwen3.8-27b
```

The API key stays on the server. Never use a `VITE_` prefix for it. The Ollama settings can stay in the file; they are unused when the provider is `groq`.

The configured Qwen model is listed in Groq's current model catalog and free-plan limits. It is a preview model, so availability may change. If Groq reports model access or availability errors, check the [supported models](https://console.groq.com/docs/models) and your account's model permissions before changing `GROQ_MODEL`. This feature is for reviewed administrative summaries, not validated clinical predictions.

## 3. Clear the configuration cache and test

Open PowerShell and run:

```powershell
cd C:\xampp\htdocs\CapstoneCurrent\CapstoneProject\reprocare
php artisan config:clear
php artisan analytics:ai-check
php artisan analytics:ai-check --connect
```

If `php` is not recognized, replace it with `C:\xampp\php\php.exe` in those commands.

- The first check reports the provider, model and whether a key is configured. It never prints the key and makes no network request.
- `--connect` sends only hard-coded, made-up statistics. It does not query patient records, create records, or send SMS.
- Success reads: **Groq connection succeeded. A complete AI response was received using synthetic data.**

An API key is required to complete this live check. Automated tests use fake Groq responses; they do not prove that your account, internet connection or API key works.

## 4. Use it in the application

1. Sign in as CHO and open **Analytics**.
2. Apply the desired date and barangay filters.
3. Under **Ask about this report**, the status should show **Online AI · Groq**. This means credentials are configured; it does not itself verify the connection.
4. Select **Priorities**, **Maternal deaths**, **Trends** or **Areas**, then click **Ask assistant**.
5. A successful generated answer is labelled **Online AI draft (Groq)**. An unavailable AI answer is explicitly labelled as the **local rules answer**, with a specific reason.

The assistant accepts free-form questions about maternal and reproductive health, ReproCare workflows, and the selected report. Your typed question is sent to Groq with grouped statistics; do not include patient names, identifiers, contacts, or notes. Common contact details and credential patterns are blocked locally. Unrelated questions receive an out-of-scope response. AI answers cannot update clinical risk classifications or send reminders automatically.

Matching successful answers can be reused for up to five minutes to conserve the free quota. The question, grouped statistics, topic and model are used to determine a match; the current local area legend is always shown. The count-based cards and graphs continue to use the exact current local report.

## What goes online

Only a strict projection of the current report is sent:

| Sent to Groq | Kept on your server |
| --- | --- |
| Your question and a topic instruction | Patient records, queue rows, contacts and notes |
| Count ranges such as `5–9` or `10–19` | Exact counts |
| `Below 5 (includes zero)` for all values 0–4 | Whether a small count is exactly 0, 1, 2, 3 or 4 |
| `Area 1`, `Area 2`, etc., for up to ten areas | Barangay names and area-to-name mapping |
| `Month 1`, `Month 2`, etc., in order | Actual date labels and filter dates |
| Definitions explaining current vs historical figures | Patient names, IDs, contacts, diagnoses, clinical notes and priority-queue rows |

The response displays a local area legend so staff can interpret area aliases. Grouping reduces disclosure but is not a guarantee of anonymity or authorization to share real health data. Start the connection check and capstone demonstration with synthetic records. Before use with actual patient records, have the responsible data owner review the exact outbound fields, provider terms and account settings. Do not mix real patients into a database assumed to contain only demo data.

The model is instructed not to infer that an event occurred from a `Below 5` value. With only a few records, it may say there is insufficient information. Use the local charts and rule-based cards for exact small counts and patient follow-up. A generated answer still needs staff review.

## Troubleshooting

| Message | What to do |
| --- | --- |
| Groq needs setup / missing key | Add `GROQ_API_KEY` to `.env`, run `php artisan config:clear`, and refresh. |
| Key or model access rejected | Check the key and model permissions in Groq. Replace a revoked key locally and clear configuration. |
| Usage limit reached | Wait at least a minute. Daily/token quota exhaustion may require waiting for the account's reset time. The app uses a one-minute cooldown after a provider 429 response. |
| Model/request unavailable | Check `GROQ_MODEL` against your account's available models. The app does not silently switch models or upgrade a plan. |
| Connection could not complete | Check internet access, firewalls, and PHP HTTPS certificates. Each request has a 20-second total timeout and falls back to local rules. |
| TLS certificate problem in XAMPP | Run `php --ini` to identify the loaded configuration. Set `curl.cainfo` and `openssl.cafile` to a valid CA bundle, such as the official [curl CA bundle](https://curl.se/docs/caextract.html), then restart Apache. Keep certificate verification enabled. |
| Incomplete answer | Try again. Truncated, empty, malformed and tool-call responses are rejected and never cached. |
| Local rules · AI off | Set `ANALYTICS_AI_PROVIDER=groq`, clear configuration, and refresh. |

To turn off cloud calls, set `ANALYTICS_AI_PROVIDER=rules` and run `php artisan config:clear`. Charts, the queue, and local suggestions continue working. You can switch back to `ollama` for the existing local-model integration.

## Developer verification

```powershell
php artisan test --filter=GroqAnalyticsTest
php artisan test
```

Coverage includes the real HTTP controller path, provider labels, outbound field restrictions, grouped counts, key handling, quota cooldown, response caching, invalid/incomplete responses, errors, and the synthetic connection check. No new PHP package, database migration or frontend build is required.
