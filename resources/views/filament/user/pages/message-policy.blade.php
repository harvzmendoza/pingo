<x-filament-panels::page>
    <div class="space-y-6">
        <section class="vite-theme-card">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/80">Messaging Compliance</p>
            <h2 class="mt-2 text-2xl font-bold text-white">Message Policy and Delivery Rules</h2>
            <p class="mt-2 text-sm text-white/90">
                Violating messages can appear as sent in your report, but they may be blocked from actual delivery.
                Credit penalties are applied automatically based on the violation type and recipient count.
            </p>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <article class="rounded-xl border border-danger-200 bg-danger-50 p-5">
                <h3 class="text-base font-semibold text-danger-800">Prohibited: URLs, domains, and links</h3>
                <p class="mt-2 text-sm text-danger-700">
                    Any link-like content is not allowed in outbound messages, including:
                </p>
                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-danger-700">
                    <li><code>http://</code>, <code>https://</code>, <code>www.</code></li>
                    <li>Domain names and subdomains (for example: <code>example.com</code>)</li>
                    <li>IP addresses and shortened URLs (for example: <code>bit.ly</code>)</li>
                </ul>
                <p class="mt-3 text-sm font-medium text-danger-800">Penalty: 10 credits per violating message per recipient.</p>
            </article>

            <article class="rounded-xl border border-danger-200 bg-danger-50 p-5">
                <h3 class="text-base font-semibold text-danger-800">Prohibited: profanity, abuse, and harassment</h3>
                <p class="mt-2 text-sm text-danger-700">
                    Messages containing profanity, hate speech, personal attacks, or bullying (Filipino or English)
                    are blocked and penalized.
                </p>
                <p class="mt-3 text-sm font-medium text-danger-800">Penalty: 50 credits per violating message per recipient.</p>
            </article>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900">Bulk campaign penalty calculation</h3>
            <p class="mt-2 text-sm text-gray-700">
                Penalties are applied per recipient. Example:
            </p>
            <div class="mt-3 rounded-lg bg-gray-50 p-3 text-sm text-gray-800">
                A message with a URL sent to 250 recipients = <strong>250 × 10 = 2,500 credits</strong> deducted.
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-3">
            <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Before You Send</h3>
                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-gray-700">
                    <li>Review content for link-like text patterns.</li>
                    <li>Avoid slang or words that may be interpreted as abusive.</li>
                    <li>Send a small test batch first.</li>
                </ul>
            </article>

            <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">What Happens on Violation</h3>
                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-gray-700">
                    <li>Delivery is blocked by policy filters.</li>
                    <li>Message status may still show as submitted/sent.</li>
                    <li>Credits are automatically deducted.</li>
                </ul>
            </article>

            <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Recommended Alternatives</h3>
                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-gray-700">
                    <li>Use plain text call-to-actions without links.</li>
                    <li>Share branded terms instead of raw URLs.</li>
                    <li>Keep tone professional and neutral.</li>
                </ul>
            </article>
        </section>
    </div>
</x-filament-panels::page>
