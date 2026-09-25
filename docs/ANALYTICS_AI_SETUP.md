# Analytics and free local AI

**Online AI is now available:** follow [Groq online AI setup](GROQ_ANALYTICS_SETUP.md) if you want the model to run on a cloud server instead of your computer. The instructions below cover the existing rules and Ollama options.

Open **CHO → Analytics**. The dashboard and its rules assistant work immediately without a subscription, API key, model download or AI service. The rules assistant is deterministic decision support, not a trained AI model. Optional Ollama adds a language-model summary on your own server. Local model use has no API usage fee; it still uses your computer's memory, storage and electricity.

## Using the dashboard

1. Select a date range (up to three years) and barangay, then click **Apply filters**.
2. Read **Suggested next steps** and the evidence shown on each card.
3. Review the **Pregnancy review queue**. The **Review** button opens the corresponding pregnancy, including walk-in records. The queue is paginated, with 15 records per page.
4. Use **Priorities**, **Maternal deaths**, **Trends** or **Areas**, then click **Ask assistant**. The answer uses the filters already applied to the report. Changing the controls without applying them does not change the assistant's selection.
5. Use **Print report** for a printable report. It includes the currently displayed queue page, not all queue pages.

Current open pregnancies and follow-up priorities are a snapshot of today in the selected area. The date range applies to historical registrations, maternal deaths and complication events. Old pregnancies with no recorded outcome remain open even when their expected delivery date has passed; staff should verify and update those records.

## Enable the optional Ollama model on Windows

Ollama was detected on this development computer, but its server could not start from the restricted workspace because its user-profile log files were not writable. Start it from your normal Windows account. You do not need this step for the dashboard or rules assistant.

1. On the computer that runs Laravel/PHP, install [Ollama for Windows](https://ollama.com/download/windows) if it is not already installed. A model running on a patient's laptop cannot be reached through the server's `127.0.0.1` address.
2. Quit the Ollama tray application if it is already running. Open PowerShell and run:

   ```powershell
   $env:OLLAMA_NO_CLOUD="1"
   ollama serve
   ```

   Keep that window open. If you get "address already in use", another Ollama server is already running. Stop that instance normally before starting this one; do not change the service to a public address.

3. In a second PowerShell window, download the starter model once:

   ```powershell
   ollama pull llama3.2:1b
   ollama list
   ```

   This is a roughly 1.3 GB download, with additional memory needed to run it. A small model is useful for testing summaries; its clinical accuracy is not validated. Internet is needed to download the model. Subsequent local inference can run offline.

4. In this project's `reprocare/.env`, add or update:

   ```dotenv
   ANALYTICS_AI_PROVIDER=ollama
   OLLAMA_URL=http://127.0.0.1:11434
   OLLAMA_MODEL=llama3.2:1b
   ```

5. In the project directory run:

   ```powershell
   cd C:\xampp\htdocs\CapstoneCurrent\CapstoneProject\reprocare
   php artisan config:clear
   ```

6. Refresh **CHO → Analytics** and ask **Summarize priorities**. A successful response is marked **Local AI draft**. If the server is stopped, the model is missing, or generation is too slow, you receive a clearly labelled answer from the local rules instead.

To return to rules only, set `ANALYTICS_AI_PROVIDER=rules`, run `php artisan config:clear`, and refresh. Existing Gemini keys are not used by this analytics feature.

## Troubleshooting

- **Still getting a rules answer:** Check `ollama list` for the exact configured model name. Check `Invoke-RestMethod http://127.0.0.1:11434/api/tags` from the server computer. If this fails, start Ollama. A cold model load or slow CPU may exceed the 20-second generation timeout; try again after the model has loaded.
- **Port already in use:** Use the existing local service or quit it normally before starting a new one. `OLLAMA_NO_CLOUD=1` applies to the server process launched from that PowerShell window; set it again when launching a new session.
- **No data or all zeros:** The report only counts saved, non-archived records matching the dates and area. Enter maternal deaths and complications using the existing RHU recording screens; the graph uses the event date, not the date someone typed the record. Confirm the patient's barangay and recorded risk in their existing forms.
- **Missing appointment flags:** Only appointments with a matching `pregnancy_id` are used. Older, unlinked appointments are not guessed into a pregnancy. Staff should verify those records separately.
- **Too many requests:** The assistant accepts up to ten requests per minute per authenticated user. Wait a minute and retry.
- **Access denied:** Analytics and its chat endpoint require an authenticated CHO or RHU account. RHU accounts also need a valid RHU assignment.
- **Browser says the session expired:** Refresh and sign in again. The chat requires the normal Laravel session and CSRF token.

## Data definitions and review

- Pregnancy registration trends use `pregnancies.created_at`, not conception date. Zero months remain visible. Pregnancy geography uses the patient's current barangay because the existing pregnancy table does not store a historical barangay snapshot.
- Death trends use `maternal_deaths.death_date` and the death record's barangay. Complications use `maternal_morbidities.event_date` and their recorded barangay. These are separate event counts and may overlap; complications are not automatically classified as confirmed maternal near misses.
- Open records have no `ended_at`, `delivery_date` or `outcome`, and no known maternal-death link to the pregnancy or patient. Passing an expected delivery date does not establish that delivery occurred.
- Priority order is an operational aid: the latest linked health record's emergency flag, the more serious of the stored pregnancy/latest linked health-record risk, then missed/overdue linked appointments, then expected delivery date. The legacy high-risk boolean is retained as a High flag. Unrecognized risk values stay **Unassessed**. No new clinical scores or treatment protocols are inferred.
- Counts are not incidence rates, mortality ratios, proof of a surge, or predictions of future deaths. Zero recorded deaths may mean incomplete reporting. Differences between barangays can reflect population size and reporting coverage.
- The analytics page never updates risk levels, creates tasks/appointments, or sends SMS. Suggestions require staff review. Qualified local clinicians should review the ordering and wording before operational use.
- Optional Ollama receives aggregate figures and operational suggestions; patient names, IDs, phone numbers and clinical notes are excluded from its context. Avoid putting patient identifiers in the question. Only a loopback Ollama endpoint is accepted; Ollama cloud model names, remote-model metadata and redirects are rejected. The separate Groq provider sends grouped statistics, area aliases, the topic and your typed question; see its setup guide. AI response text is rendered as text, not HTML.

## Verification

The automated suite uses isolated in-memory SQLite data, not the live database:

```powershell
php artisan test --filter=MaternalAnalyticsTest
php artisan test
```

Coverage includes empty and populated page rendering, event dates, area filters, walk-in patients, overdue pregnancies, known deaths, archived records, latest-pregnancy assessments, queue ordering, authorization, invalid date ranges, aggregate-only AI payloads and unavailable/remote AI fallback. No database migration or new package is required for this feature on the current project schema.

Reference: [Ollama local-only mode](https://docs.ollama.com/faq), [Ollama chat API](https://docs.ollama.com/api/chat), [starter model](https://ollama.com/library/llama3.2:1b). Human oversight follows the principles described in [WHO's guidance on AI in health](https://www.who.int/news/item/28-06-2021-who-issues-first-global-report-on-ai-in-health-and-six-guiding-principles-for-its-design-and-use).

## CHO and RHU analytics scope

- CHO > Analytics covers the city, with RHU 1-5 and barangay filters. City-wide totals include unmapped areas.
- RHU > Analytics is restricted to the signed-in account's rhu_assignment and the active barangays mapped to it. Changing the URL cannot select another RHU.
- Risk heat maps are inside Analytics. Old map URLs redirect there. The map, charts, queue and assistant use the same applied scope.
- Assign barangays to their correct RHU in the existing barangays table (rhu_assignment), and assign RHU staff accounts in user management. RHUs with no mapped barangays show a setup notice rather than city-wide data. Do not infer coverage from an RHU name or a patient's recording staff member.
- The current database has RHU 1 mappings only. Configure the verified RHU 2-5 catchments before using those filters. Barangay names must match saved records, ignoring case and repeated whitespace.
- Maps use stored purok latitude/longitude, grouped into approximate barangay locations. Missing coordinates are listed, never invented. The tables remain usable if map tiles cannot load.
- Purple means pregnancy registrations; green means Low risk, amber means Medium risk or complications, red means High/Critical risk or deaths. Gray means unassessed or no matching records. Deaths are striped in the area comparison to distinguish them from current high-risk counts.
- General questions now work with Groq: for example, "Explain breastfeeding support" or "How can our RHU organize follow-up based on this report?" Unrelated questions are declined. This remains reviewed decision support; it cannot prescribe individual treatment or change records.
- Your question goes to Groq. Do not include patient details. Registry data stays projected into bands and aliases. Local rules continue to work if Groq is unavailable.
