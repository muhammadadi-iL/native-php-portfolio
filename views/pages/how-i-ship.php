<div class="captcha-gate" id="captcha-gate" hidden>
<div class="captcha-card" role="dialog" aria-modal="true" aria-labelledby="captcha-title">
<span class="mark" aria-hidden="true"><img src="/favicon.svg?v=3" alt="" width="46" height="46"></span>
<p class="eyebrow">Quick check</p>
<h2 id="captcha-title">Confirm you are a person</h2>
<div id="captcha-widget"></div>
<p class="captcha-error" hidden>That check did not pass. Try again.</p>
</div>
</div>
<div class="aurora" aria-hidden="true"></div>
<a class="skip" href="#main">Skip to content</a>
<div class="read-bar" aria-hidden="true"></div>
<header class="site-header">
<div class="wrap bar">
<a class="brand" href="/"><span class="mark" aria-hidden="true"><img src="/favicon.svg?v=3" alt="" width="46" height="46"></span><span class="brand-copy"><span class="brand-name">Muhammad Adil</span><span class="brand-role">Senior Backend Developer</span></span></a>
<nav aria-label="Main"><a href="/work/">Work</a><a href="/how-i-ship/" aria-current="page">Process</a><a href="/about/">About</a><a class="contact-link" href="#contact">Contact</a></nav>
<div class="tools"><div class="lang" data-no-i18n><button class="lang-toggle" type="button" data-lang-toggle aria-expanded="false" aria-controls="lang-menu" aria-label="Language"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M4 12h16M12 4c2.2 2.4 3.3 5.1 3.3 8s-1.1 5.6-3.3 8c-2.2-2.4-3.3-5.1-3.3-8s1.1-5.6 3.3-8z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg><span data-lang-code>EN</span></button><div class="lang-menu" id="lang-menu" hidden><button type="button" data-lang="en" class="is-current">English<span>EN</span></button><button type="button" data-lang="ar">العربية<span>AR</span></button><button type="button" data-lang="fr">Français<span>FR</span></button><button type="button" data-lang="nl">Nederlands<span>NL</span></button><button type="button" data-lang="ja">日本語<span>JA</span></button></div></div><button class="theme-toggle" type="button" aria-label="Switch between light and dark" data-theme-toggle>
<svg class="sun" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.8"></circle><path d="M12 2v2.5M12 19.5V22M2 12h2.5M19.5 12H22M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M4.9 19.1l1.8-1.8M17.3 6.7l1.8-1.8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path></svg>
<svg class="moon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"></path></svg>
</button></div>
</div>
</header>
<main id="main">

<section class="page-head ship" data-rail="Process">
<div class="wrap ship-wrap">
<p class="ship-kicker">Process</p>
<h1>How I ship — a spec-driven workflow for AI coding agents</h1>
<p class="lead">I built a workflow that cuts a Figma file and a business analysis into small feature packs, then has an AI agent implement each one under architecture rules it cannot renegotiate. I use it on my own projects and taught it to other developers.</p>

<h2>Why I built it</h2>
<p>Ask an AI agent to build the orders module and you get code that looks plausible and matches neither the design nor the business rules. Every session also invents its own architecture, so the tenth feature does not look like the first.</p>
<p>The model is rarely the problem. The input is. A whole Figma file and a long analysis document are too big and too vague to implement from, for an agent or for a person.</p>
<p>So the workflow does two things. It cuts the project into pieces small enough to leave no room for guessing, and it gives the agent rules that stay the same from the first feature to the last.</p>

<h2>Stage 1: from design and analysis to feature packs</h2>
<ol class="spec-card">
<li><span class="spec-n">01</span><div><strong>Pull the Figma file as JSON</strong><span>A script calls the Figma API and saves the raw file.</span></div></li>
<li><span class="spec-n">02</span><div><strong>Reduce it to a spec</strong><span>A second script keeps what a backend needs to know (screens, fields, flows) and drops the drawing data.</span></div></li>
<li><span class="spec-n">03</span><div><strong>Split by actor</strong><span>Customer, provider, representative, admin. The Figma spec and the business analysis are both split, so each actor has its own screens file and its own business file.</span></div></li>
<li><span class="spec-n">04</span><div><strong>Split each actor into features</strong><span>Three separate agent runs with one job each: scan and index, extract the fragments, build the packs.</span></div></li>
<li class="is-done"><span class="spec-n">05</span><div><strong>A feature pack per feature</strong><span>Screens, business rules and an execution prompt, in one numbered folder.</span></div></li>
<li class="spec-note">The output is one folder per feature, ready to be implemented without the rest of the project in view.</li>
</ol>
<p>A feature pack holds three files:</p>
<ul class="ship-points">
<li><strong>The screens of this feature</strong>, as a fragment of the Figma spec.</li>
<li><strong>The business rules of this feature</strong>, as a fragment of the analysis.</li>
<li><strong>An execution prompt</strong>: what to build, which features it depends on, and where each requirement comes from.</li>
</ul>
<p>Two rules are written into every pack. When the design and the business analysis disagree, the business analysis wins. And the agent may not invent a business rule that is not in the pack. Every field and rule carries a tag saying whether it came from the analysis or from the design.</p>

<h2>Stage 2: implementing a feature</h2>
<p>The project itself carries the rules. I call the main file the constitution. It is about 1,500 lines and fixes the things that should never vary between features: layering, naming, the shape of DTOs and requests, the response envelope, translations, API documentation, query performance and seed data.</p>
<p>One decision is taken once per project and then locked: whether the code uses a repository layer or a leaner path from service to model. The agent asks on the first feature, records the answer and never asks again.</p>
<p>To build a feature I name its folder and say “execute”.</p>
<ol class="spec-card">
<li><span class="spec-n">01</span><div><strong>Read the pack and the constitution, then open a branch</strong></div></li>
<li><span class="spec-n">02</span><div><strong>Build in a fixed order</strong><span>Migrations, enums, DTOs, repositories, services, requests, resources, controllers, routes.</span></div></li>
<li><span class="spec-n">03</span><div><strong>Sync the API collection after every endpoint</strong><span>The work does not continue until the request exists in the shared collection.</span></div></li>
<li><span class="spec-n">04</span><div><strong>Admin screens, translations, feature tests, API documentation</strong></div></li>
<li><span class="spec-n">05</span><div><strong>Run the quality gate</strong><span>A checklist of about 25 items. Every unchecked box is fixed before the feature counts as complete.</span></div></li>
<li class="is-done"><span class="spec-n">06</span><div><strong>Commit, push and open a merge request</strong><span>The pack then moves to a finished folder, so the list of open features is always the list of remaining work.</span></div></li>
</ol>
<p>The same order for every feature, on every project.</p>
<p>The same folder name with the word “review” starts a different mode. The agent compares existing code with the pack and the constitution, refactors what breaks the rules and generates whatever is missing. That makes the workflow usable on a project that is already half built.</p>

<h2>What I added on the latest project</h2>
<p>On Al-Kout I kept the feature packs and changed how the work is driven.</p>
<ul class="ship-points">
<li><strong>Delivery cycles.</strong> Features are grouped into cycles. Each cycle starts with a planning agent that writes a plan: the shared entities, the state machine, the order of pull requests, an acceptance scenario and a list of questions for me. I answer all the questions in one go, before any code is written.</li>
<li><strong>Separate agents for planning, implementing and reviewing.</strong> No agent reviews its own work.</li>
<li><strong>Triage before code.</strong> A pack that describes a navigation shell or another screen-only feature is classified as a frontend reference, and no backend code is written for it.</li>
<li><strong>One register for deferred work.</strong> Every agreed deferral has an identifier, a reason and a description of what remains. The agent may not record an approval I did not give.</li>
<li><strong>Skills.</strong> Thirteen small, reusable procedures such as generating a DTO, creating a repository or reviewing an endpoint.</li>
</ul>

<h2>Where I stay in the loop</h2>
<p>Two rules matter more than the rest:</p>
<ul class="ship-points">
<li>A question the plan does not cover stops the work. The agent asks. It does not assume.</li>
<li>Nothing is merged without my explicit decision.</li>
</ul>
<p>Beyond those, the division is simple. I choose the architecture and write the rules. I answer the business questions. I review each pull request and run the full test suite myself. The agent does the typing, in an order that does not change.</p>

<h2>What it has produced</h2>
<ul class="ship-points">
<li>Msane, the first full project built this way: a B2B marketplace delivered in about seven and a half weeks with one backend developer.</li>
<li>Al-Kout: 123 feature packs across three kinds of user, delivered in eight cycles over four weeks, with about 1,800 test methods.</li>
<li>I taught the workflow to other developers, who went on to use it on their own projects.</li>
</ul>

<h2>What it does not solve</h2>
<p>Speed moves the bottleneck. Review becomes the slow step, and it has to stay careful. External integrations such as payment gateways and SMS providers still wait on accounts, credentials and testing against the real service. And code that is correct in a test suite still needs to be verified against a real database under concurrent load.</p>
</div>
</section>

</main>
<footer id="contact" class="contact" data-rail="Contact">
<div class="wrap">
<div class="grid">
<div class="intro">
<p class="eyebrow">Contact</p>
<h2>Get in touch</h2>
<p class="muted">Open to senior backend roles. Email is the fastest way to reach me.</p>
<div class="btn-row">
<a class="btn btn-primary" href="https://api.whatsapp.com/send/?phone=923180253146&amp;text=Hello%20Muhammad%2C%20I%20saw%20your%20portfolio%20and%20wanted%20to%20reach%20you.&amp;type=phone_number&amp;app_absent=0" rel="noopener" target="_blank">Message on WhatsApp</a>
<a class="btn" href="/cv/Muhammad-Adil-Resume.pdf" download>Download Resume (PDF)</a>
<button type="button" class="btn" data-cv-preview>Preview Resume</button>
</div>
</div>
<dl class="details">
<div><dt>Email</dt><dd><a href="mailto:adilm0616@gmail.com">adilm0616@gmail.com</a></dd></div>
<div><dt>Phone</dt><dd><a href="tel:+923180253146">+92 318 0253146</a></dd></div>
<div><dt>WhatsApp</dt><dd><a href="https://api.whatsapp.com/send/?phone=923180253146&amp;text=Hello%20Muhammad%2C%20I%20saw%20your%20portfolio%20and%20wanted%20to%20reach%20you.&amp;type=phone_number&amp;app_absent=0" rel="noopener" target="_blank">Message on WhatsApp</a></dd></div>
<div><dt>Elsewhere</dt><dd class="links"><a class="icon-link" href="https://github.com/muhammadadi-iL" rel="me noopener" target="_blank" aria-label="GitHub"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M12 2C6.5 2 2 6.6 2 12.2c0 4.5 2.9 8.3 6.9 9.6.5.1.7-.2.7-.5v-1.7c-2.8.6-3.4-1.2-3.4-1.2-.4-1.1-1.1-1.4-1.1-1.4-.9-.6.1-.6.1-.6 1 .1 1.5 1 1.5 1 .9 1.6 2.4 1.1 3 .9.1-.7.4-1.1.6-1.4-2.2-.3-4.6-1.1-4.6-5.1 0-1.1.4-2 1-2.7-.1-.3-.4-1.3.1-2.7 0 0 .8-.3 2.8 1a9.4 9.4 0 0 1 5 0c2-1.3 2.8-1 2.8-1 .5 1.4.2 2.4.1 2.7.7.7 1 1.6 1 2.7 0 4-2.4 4.8-4.6 5.1.4.3.7.9.7 1.9v2.8c0 .3.2.6.7.5 4-1.3 6.9-5.1 6.9-9.6C22 6.6 17.5 2 12 2z"/></svg></a><a class="icon-link" href="https://www.linkedin.com/in/muhammad-adil-khan-7274a1243" rel="me noopener" target="_blank" aria-label="LinkedIn"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M5.7 8.7H2.9V21h2.8V8.7zM4.3 3C3.3 3 2.5 3.8 2.5 4.8S3.3 6.6 4.3 6.6 6.1 5.8 6.1 4.8 5.3 3 4.3 3zM21.1 21h-2.8v-6.4c0-1.8-.8-2.4-1.8-2.4s-1.9.8-1.9 2.5V21H11.8V8.7h2.7v1.7c.5-.9 1.6-1.9 3.3-1.9 2.2 0 3.3 1.4 3.3 4.2V21z"/></svg></a></dd></div>
</dl>
</div>
<p class="fine">© 2026 Muhammad Adil · Karachi, Pakistan · Open to remote</p>
</div>
</footer>
<div class="cookie-bar" id="cookie-bar" role="dialog" aria-labelledby="cookie-title">
<p class="cookie-kicker" id="cookie-title">Privacy preferences</p>
<p class="cookie-copy">This site counts visits so I can see which pages people read: your IP address, approximate location (from that address), device and the pages you open. With your permission, it also remembers your browser between visits, and the chat can note how you found the site.</p>
<button type="button" class="cookie-btn" data-cookie="all">Accept all</button>
<button type="button" class="cookie-btn" data-cookie="necessary">Necessary only</button>
<button type="button" class="cookie-manage" data-cookie-manage>Manage preferences</button>
</div>
<div class="cookie-modal" id="cookie-modal" hidden>
<div class="cookie-modal-card" role="dialog" aria-modal="true" aria-labelledby="cookie-modal-title">
<button type="button" class="cookie-close" data-cookie-close aria-label="Close"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path></svg></button>
<p class="cookie-kicker" id="cookie-modal-title">Privacy preferences</p>
<p class="cookie-copy">This site counts visits so I can see which pages people read: your IP address, approximate location (from that address), device and the pages you open. With your permission, it also remembers your browser between visits, and the chat can note how you found the site.</p>
<div class="cookie-option">
<div class="cookie-option-head"><h3>Necessary</h3><span class="cookie-always">Always on</span></div>
<p>Your theme, your language, this chat session, and the visit count described above. The site needs them, so they are always on.</p>
</div>
<div class="cookie-option">
<div class="cookie-option-head"><h3>Remember me between visits</h3><button type="button" class="cookie-switch" id="cookie-remember" role="switch" aria-checked="false" aria-label="Remember me between visits"></button></div>
<p>A random ID stored in your browser, so a return visit is recognised, and your region, referring site, landing page and device attached to your chat messages. Off unless you turn it on.</p>
</div>
<p class="cookie-note">No advertising cookies and no third-party analytics.</p>
<p class="cookie-note">Your location is never read unless you allow it in your browser's own permission prompt.</p>
<p><a class="cookie-policy" href="/privacy/">Privacy Policy</a></p>
<div class="cookie-modal-actions">
<button type="button" class="cookie-btn cookie-btn-fill" data-cookie="save">Save preferences</button>
<button type="button" class="cookie-btn" data-cookie="all">Accept all</button>
<button type="button" class="cookie-btn" data-cookie="necessary">Necessary only</button>
</div>
</div>
</div>
<div class="chat">
<section class="chat-panel" id="chat-panel" hidden>
<header class="chat-head">
<span class="mark" aria-hidden="true"><img src="/favicon.svg?v=3" alt="" width="46" height="46"></span>
<div>
<p class="chat-name">Muhammad Adil</p>
<p class="status"><span class="pulse" aria-hidden="true"></span> Open to remote</p>
<p class="chat-time">Local time in Karachi, Pakistan: <time data-no-i18n datetime="">00:00</time></p>
</div>
<a class="chat-wa" href="https://api.whatsapp.com/send/?phone=923180253146&amp;text=Hello%20Muhammad%2C%20I%20saw%20your%20portfolio%20and%20wanted%20to%20reach%20you.&amp;type=phone_number&amp;app_absent=0" rel="noopener" target="_blank" aria-label="WhatsApp"><svg class="wa-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><circle cx="12" cy="12" r="12" fill="#25D366"/><path fill="#fff" d="M17.47 14.73c-.25.7-1.45 1.34-2 1.42-.52.08-1.16.12-1.87-.12-.43-.14-.98-.32-1.68-.63-2.96-1.28-4.89-4.27-5.04-4.47-.15-.2-1.2-1.59-1.2-3.03s.76-2.15 1.03-2.45c.25-.28.66-.41 1.05-.41.13 0 .25 0 .36.01.32.01.48-.02.72.55.25.62.86 2.13.93 2.28.07.16.12.35.02.56-.1.21-.15.34-.29.52-.14.18-.31.4-.44.53-.14.14-.29.3-.12.57.16.28.72 1.2 1.56 1.94 1.07.95 1.97 1.25 2.27 1.39.28.13.44.11.61-.07.16-.19.7-.81.89-1.09.18-.28.37-.23.62-.14.25.09 1.59.75 1.86.89.27.13.45.2.52.32.07.11.07.66-.18 1.36z"/></svg></a>
<button type="button" class="chat-close" data-chat-close aria-label="Close"><svg class="close-icon" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" d="M6.5 6.5l11 11M17.5 6.5l-11 11"/></svg></button>
</header>
<p class="chat-note">An assistant answers from this site. Muhammad reads the saved chat and WhatsApp.</p>
<div class="chat-log" aria-live="polite"></div>
<form class="chat-form">
<div class="chat-fields">
<label for="chat-name">Name</label>
<input id="chat-name" type="text" autocomplete="name" maxlength="80">
<div class="chat-fields-row">
<div>
<label for="chat-phone">Phone</label>
<input id="chat-phone" type="tel" autocomplete="tel" maxlength="40" inputmode="tel">
</div>
<div>
<label for="chat-email">Email</label>
<input id="chat-email" type="text" autocomplete="email" maxlength="120" inputmode="email">
</div>
</div>
</div>
<p class="chat-error" id="chat-error" hidden></p>
<label for="chat-message">Your message</label>
<textarea id="chat-message" rows="2"></textarea>
<div class="chat-actions">
<button class="chat-send" type="submit">Send</button>
<a class="chat-handoff" href="https://api.whatsapp.com/send/?phone=923180253146&amp;text=Hello%20Muhammad%2C%20I%20saw%20your%20portfolio%20and%20wanted%20to%20reach%20you.&amp;type=phone_number&amp;app_absent=0" rel="noopener" target="_blank"><svg class="wa-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><circle cx="12" cy="12" r="12" fill="#25D366"/><path fill="#fff" d="M17.47 14.73c-.25.7-1.45 1.34-2 1.42-.52.08-1.16.12-1.87-.12-.43-.14-.98-.32-1.68-.63-2.96-1.28-4.89-4.27-5.04-4.47-.15-.2-1.2-1.59-1.2-3.03s.76-2.15 1.03-2.45c.25-.28.66-.41 1.05-.41.13 0 .25 0 .36.01.32.01.48-.02.72.55.25.62.86 2.13.93 2.28.07.16.12.35.02.56-.1.21-.15.34-.29.52-.14.18-.31.4-.44.53-.14.14-.29.3-.12.57.16.28.72 1.2 1.56 1.94 1.07.95 1.97 1.25 2.27 1.39.28.13.44.11.61-.07.16-.19.7-.81.89-1.09.18-.28.37-.23.62-.14.25.09 1.59.75 1.86.89.27.13.45.2.52.32.07.11.07.66-.18 1.36z"/></svg><span>WhatsApp</span></a>
</div>
</form>
</section>
<button class="chat-toggle" type="button" aria-expanded="false" aria-controls="chat-panel" aria-label="Open chat"><svg viewBox="0 0 32 32" width="28" height="28" aria-hidden="true"><path fill="currentColor" d="M7 6.2h15.6a3.4 3.4 0 0 1 3.4 3.4v8.4a3.4 3.4 0 0 1-3.4 3.4h-7.6L9 26.6a1.1 1.1 0 0 1-1.8-.9v-4.3H7a3.4 3.4 0 0 1-3.4-3.4V9.6A3.4 3.4 0 0 1 7 6.2Z"/><circle cx="11.2" cy="14.3" r="1.45" fill="#5b35f0"/><circle cx="16" cy="14.3" r="1.45" fill="#5b35f0"/><circle cx="20.8" cy="14.3" r="1.45" fill="#5b35f0"/></svg></button>
</div>
<nav class="rail" aria-label="Sections"></nav>
<dialog id="cv-preview" class="cv-dialog" aria-labelledby="cv-preview-title">
<div class="bar">
<h2 id="cv-preview-title">Resume</h2>
<a class="btn btn-primary" href="/cv/Muhammad-Adil-Resume.pdf" download>Download PDF</a>
<button type="button" class="close" data-cv-close aria-label="Close"><svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg></button>
</div>
<iframe class="frame" src="/cv/" title="Muhammad Adil Resume"></iframe>
</dialog>
