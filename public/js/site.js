(function () {
  var root = document.documentElement;

  function wakeMotion() {
    if (root.classList.contains("motion")) {
      root.classList.remove("motion");
      void root.offsetWidth;
    }
    root.classList.add("motion");
  }
  requestAnimationFrame(function () { requestAnimationFrame(wakeMotion); });
  window.addEventListener("pageshow", wakeMotion);
  var dictionaries = {
    ar: window.AR || {},
    fr: window.FR || {},
    nl: window.NL || {},
    ja: window.JA || {}
  };
  var langMeta = {
    en: { code: "EN", dir: "ltr" },
    ar: { code: "AR", dir: "rtl" },
    fr: { code: "FR", dir: "ltr" },
    nl: { code: "NL", dir: "ltr" },
    ja: { code: "JA", dir: "ltr" }
  };

  function matchesKey(event, name) {
    if (String(event.key || "").toLowerCase() === name) return true;
    return event.code === "Key" + name.toUpperCase();
  }
  document.addEventListener("contextmenu", function (event) {
    event.preventDefault();
  }, true);
  document.addEventListener("keydown", function (event) {
    var mod = event.ctrlKey || event.metaKey;
    var sourceKey = event.key === "F12" || event.code === "F12" ||
      (mod && (matchesKey(event, "u") || matchesKey(event, "s"))) ||
      (mod && event.shiftKey && (matchesKey(event, "i") || matchesKey(event, "j") || matchesKey(event, "c") || matchesKey(event, "k"))) ||
      (event.metaKey && event.altKey && (matchesKey(event, "u") || matchesKey(event, "i") || matchesKey(event, "j") || matchesKey(event, "c")));
    if (!sourceKey) return;
    event.preventDefault();
    event.stopPropagation();
  }, true);

  function currentTheme() {
    return root.dataset.theme === "light" ? "light" : "dark";
  }

  document.querySelectorAll("[data-theme-toggle]").forEach(function (button) {
    button.addEventListener("click", function () {
      var next = currentTheme() === "dark" ? "light" : "dark";
      root.dataset.theme = next;
      try {
        localStorage.setItem("theme", next);
      } catch (e) {}
      rememberPrefs();
    });
  });

  function norm(value) {
    return value.replace(/\s+/g, " ").trim();
  }

  function t(text) {
    var dict = dictionaries[root.dataset.lang] || {};
    var key = norm(text);
    return dict[key] || text;
  }

  function applyLang(lang) {
    if (!langMeta[lang]) lang = "en";
    var meta = langMeta[lang];
    var dict = dictionaries[lang] || {};
    root.lang = lang;
    root.dir = meta.dir;
    if (lang === "en") delete root.dataset.lang;
    else root.dataset.lang = lang;

    var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
    var node;
    while ((node = walker.nextNode())) {
      var parent = node.parentElement;
      if (!parent || parent.closest("script, style, [data-no-i18n]")) continue;
      if (node.__en == null) node.__en = node.nodeValue;
      var original = node.__en;
      var key = norm(original);
      if (!key) continue;
      var lead = original.match(/^\s*/)[0];
      var tail = original.match(/\s*$/)[0];
      node.nodeValue = dict[key] ? lead + dict[key] + tail : original;
    }

    document.querySelectorAll("[alt], [aria-label], [title]").forEach(function (el) {
      if (el.closest("[data-no-i18n]")) return;
      ["alt", "aria-label", "title"].forEach(function (attr) {
        if (!el.hasAttribute(attr)) return;
        if (!el._i18n) el._i18n = {};
        if (el._i18n[attr] == null) el._i18n[attr] = el.getAttribute(attr);
        var key = norm(el._i18n[attr]);
        el.setAttribute(attr, dict[key] || el._i18n[attr]);
      });
    });

    if (!root.dataset.enTitle) root.dataset.enTitle = document.title;
    var titleKey = norm(root.dataset.enTitle);
    document.title = dict[titleKey] || root.dataset.enTitle;

    document.querySelectorAll("[data-lang-code]").forEach(function (el) {
      el.textContent = meta.code;
    });
    document.querySelectorAll("[data-lang]").forEach(function (button) {
      button.classList.toggle("is-current", button.getAttribute("data-lang") === lang);
    });

    root.classList.add("i18n-ready");
  }

  document.querySelectorAll(".lang").forEach(function (box) {
    var toggle = box.querySelector("[data-lang-toggle]");
    var menu = box.querySelector(".lang-menu");
    var frame = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    var shape = document.createElementNS("http://www.w3.org/2000/svg", "polygon");
    frame.setAttribute("class", "lang-frame");
    frame.setAttribute("aria-hidden", "true");
    frame.appendChild(shape);
    if (menu) menu.insertBefore(frame, menu.firstChild);
    function drawLangFrame() {
      if (!menu) return;
      var width = menu.clientWidth;
      var height = menu.clientHeight;
      if (!width || !height) return;
      var topCut = 12;
      var bottomCut = 18;
      var rtl = document.documentElement.dir === "rtl";
      var points = rtl
        ? [[topCut, 0], [width, 0], [width, height - bottomCut], [width - bottomCut, height], [0, height], [0, topCut]]
        : [[0, 0], [width - topCut, 0], [width, topCut], [width, height], [bottomCut, height], [0, height - bottomCut]];
      frame.setAttribute("viewBox", "0 0 " + width + " " + height);
      shape.setAttribute("points", points.map(function (point) { return point.join(","); }).join(" "));
    }
    function setMenu(open) {
      if (!menu || !toggle) return;
      menu.hidden = !open;
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
      if (open) requestAnimationFrame(drawLangFrame);
    }
    if (toggle) {
      toggle.addEventListener("click", function () {
        setMenu(menu.hidden);
      });
    }
    box.querySelectorAll("[data-lang]").forEach(function (button) {
      button.addEventListener("click", function () {
        var next = button.getAttribute("data-lang");
        try { localStorage.setItem("lang", next); } catch (e) {}
        applyLang(next);
        rememberPrefs();
        setMenu(false);
      });
    });
    document.addEventListener("click", function (event) {
      if (!box.contains(event.target)) setMenu(false);
    });
    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") setMenu(false);
    });
  });

  if (root.dataset.lang && langMeta[root.dataset.lang]) applyLang(root.dataset.lang);
  else root.classList.add("i18n-ready");

  var bar = document.querySelector(".read-bar");
  if (bar) {
    var ticking = false;
    function paintBar() {
      var height = document.documentElement.scrollHeight - window.innerHeight;
      var ratio = height > 0 ? window.scrollY / height : 0;
      bar.style.transform = "scaleX(" + Math.min(1, Math.max(0, ratio)) + ")";
      ticking = false;
    }
    window.addEventListener("scroll", function () {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(paintBar);
    }, { passive: true });
    paintBar();
  }

  var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  var rail = document.querySelector(".rail");
  var spots = Array.prototype.slice.call(document.querySelectorAll("[data-rail]"));
  if (rail && spots.length > 1) {
    var frame = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    frame.setAttribute("class", "rail-frame");
    frame.setAttribute("aria-hidden", "true");
    var shape = document.createElementNS("http://www.w3.org/2000/svg", "polygon");
    frame.appendChild(shape);
    rail.appendChild(frame);
    var track = document.createElement("span");
    track.className = "rail-track";
    track.setAttribute("aria-hidden", "true");
    rail.appendChild(track);
    spots.forEach(function (el, index) {
      if (!el.id) el.id = "section-" + index;
      var link = document.createElement("a");
      link.href = "#" + el.id;
      var label = document.createElement("span");
      label.textContent = el.getAttribute("data-rail");
      var mark = document.createElement("i");
      mark.setAttribute("aria-hidden", "true");
      link.appendChild(label);
      link.appendChild(mark);
      rail.appendChild(link);
    });
    var links = rail.querySelectorAll("a");
    function markRail() {
      var doc = document.documentElement;
      var atEnd = window.scrollY + window.innerHeight >= doc.scrollHeight - 2;
      var line = window.scrollY + window.innerHeight * 0.34;
      var current = atEnd ? spots.length - 1 : 0;
      if (!atEnd) {
        spots.forEach(function (el, index) {
          var top = el.getBoundingClientRect().top + window.scrollY;
          if (top <= line) current = index;
        });
      }
      links.forEach(function (link, index) {
        if (index === current) link.classList.add("is-current");
        else link.classList.remove("is-current");
      });
      var span = links.length > 1 ? (current / (links.length - 1)) * 100 : 0;
      rail.style.setProperty("--rail-progress", span + "%");
    }
    function drawFrame() {
      var width = rail.clientWidth;
      var height = rail.clientHeight;
      if (!width || !height) return;
      var topCut = 14;
      var bottomCut = 28;
      var rtl = document.documentElement.dir === "rtl";
      var points = rtl
        ? [[topCut, 0], [width, 0], [width, height - bottomCut], [width - bottomCut, height], [0, height], [0, topCut]]
        : [[0, 0], [width - topCut, 0], [width, topCut], [width, height], [bottomCut, height], [0, height - bottomCut]];
      frame.setAttribute("viewBox", "0 0 " + width + " " + height);
      shape.setAttribute("points", points.map(function (point) { return point.join(","); }).join(" "));
    }
    if ("ResizeObserver" in window) new ResizeObserver(drawFrame).observe(rail);
    rail.addEventListener("mouseenter", drawFrame);
    drawFrame();
    window.addEventListener("scroll", markRail, { passive: true });
    markRail();
  } else if (rail) {
    rail.hidden = true;
  }

  var chat = document.querySelector(".chat");
  if (chat) {
    var panel = chat.querySelector(".chat-panel");
    var toggle = chat.querySelector(".chat-toggle");
    var clock = chat.querySelector("time");
    function showClock() {
      if (!clock) return;
      var value = new Intl.DateTimeFormat("en-GB", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
        timeZone: "Asia/Karachi"
      }).format(new Date());
      clock.textContent = value;
      clock.dateTime = value;
    }
    showClock();
    setInterval(showClock, 30000);
    function setChat(open) {
      if (!panel || !toggle) return;
      panel.hidden = !open;
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
      if (open) {
        chat.querySelectorAll(".pulse").forEach(function (dot) {
          dot.style.animation = "none";
          void dot.offsetWidth;
          dot.style.animation = "";
        });
      }
      if (open && log && !greeted) {
        greeted = true;
        addLine(t("Ask about Muhammad's work, his stack, or how to reach him."), "from-bot");
      }
      if (open && nameField && !nameField.value.trim()) nameField.focus();
      else if (open && field) field.focus();
    }
    toggle.addEventListener("click", function () {
      setChat(panel.hidden);
    });
    chat.querySelectorAll("[data-chat-close]").forEach(function (button) {
      button.addEventListener("click", function () { setChat(false); });
    });
    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") setChat(false);
    });
    var log = chat.querySelector(".chat-log");
    var form = chat.querySelector(".chat-form");
    var field = chat.querySelector("#chat-message");
    var nameField = chat.querySelector("#chat-name");
    var phoneField = chat.querySelector("#chat-phone");
    var emailField = chat.querySelector("#chat-email");
    var errorLine = chat.querySelector("#chat-error");
    var handoff = chat.querySelector(".chat-handoff");
    function storedContact(key) {
      try { return sessionStorage.getItem(key) || ""; } catch (error) { return ""; }
    }
    if (nameField) nameField.value = storedContact("adil-chat-name");
    if (phoneField) phoneField.value = storedContact("adil-chat-phone");
    if (emailField) emailField.value = storedContact("adil-chat-email");
    function showChatError(message) {
      if (!errorLine) return;
      errorLine.hidden = !message;
      errorLine.textContent = message ? t(message) : "";
    }
    function contactReady() {
      var person = nameField ? nameField.value.trim() : "";
      var email = emailField ? emailField.value.trim() : "";
      if (!person) {
        showChatError("Add your name so Muhammad knows who wrote.");
        if (nameField) nameField.focus();
        return false;
      }
      if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showChatError("That email address does not look right.");
        if (emailField) emailField.focus();
        return false;
      }
      showChatError("");
      try {
        sessionStorage.setItem("adil-chat-name", person);
        sessionStorage.setItem("adil-chat-phone", phoneField ? phoneField.value.trim() : "");
        sessionStorage.setItem("adil-chat-email", email);
      } catch (error) {}
      return true;
    }
    var greeted = false;
    function speakerName(kind) {
      if (kind === "from-user") {
        var person = nameField ? nameField.value.trim() : "";
        return person || "You";
      }
      return "Muhammad Adil";
    }
    function addLine(text, kind) {
      var line = document.createElement("div");
      line.className = "bubble " + kind;
      var who = document.createElement("span");
      who.className = "who";
      who.textContent = speakerName(kind);
      var body = document.createElement("p");
      body.textContent = text;
      line.append(who, body);
      log.appendChild(line);
      log.scrollTop = log.scrollHeight;
      return line;
    }
    function setLine(line, text) {
      line.classList.remove("is-typing");
      line.removeAttribute("aria-busy");
      var icon = line.querySelector(":scope > .mark");
      if (icon) icon.remove();
      var copy = line.querySelector(":scope > .typing-copy");
      if (copy) {
        while (copy.firstChild) line.appendChild(copy.firstChild);
        copy.remove();
      }
      var body = line.querySelector("p");
      if (body) body.textContent = text;
      log.scrollTop = log.scrollHeight;
    }
    function addTyping() {
      var line = addLine("", "from-bot");
      line.classList.add("is-typing");
      line.setAttribute("aria-busy", "true");
      var source = chat.querySelector(".chat-head .mark");
      if (source) line.insertBefore(source.cloneNode(true), line.firstChild);
      var copy = document.createElement("div");
      copy.className = "typing-copy";
      var who = line.querySelector(".who");
      var body = line.querySelector("p");
      copy.append(who, body);
      line.appendChild(copy);
      var dots = document.createElement("span");
      dots.className = "typing";
      dots.setAttribute("aria-hidden", "true");
      for (var i = 0; i < 3; i += 1) dots.appendChild(document.createElement("i"));
      body.appendChild(dots);
      log.scrollTop = log.scrollHeight;
      return line;
    }
    function answer(raw) {
      var q = raw.toLowerCase();
      function has(words) {
        return words.some(function (word) { return q.indexOf(word) !== -1; });
      }
      if (has(["hello", "hi", "hey", "salam", "assalam"])) return "Hello. Ask about Muhammad's work, his stack, or how to reach him.";
      if (has(["email", "e-mail", "gmail"])) return "Email him at adilm0616@gmail.com.";
      if (has(["whatsapp", "phone", "number", "call"])) return "WhatsApp and phone: +92 318 0253146.";
      if (has(["where", "karachi", "location", "remote", "hire", "available", "role"])) return "He is in Karachi and open to remote senior backend roles.";
      if (has(["resume", "cv"])) return "Use Download Resume or Preview Resume on this site.";
      if (has(["language", "urdu", "english", "french", "arabic", "dutch", "japanese"])) return "English and Urdu. Urdu is native. This site also reads in Arabic, French, Dutch and Japanese.";
      if (has(["valora"])) return "VALORA is home-care software. Clients, visits, billing and payroll share one record. Agents draft the paperwork and a person approves it.";
      if (has(["food link", "foodlink"])) return "Food Link is multi-tenant ordering. A new merchant gets its own database, and that step cannot be one transaction.";
      if (has(["msane"])) return "Msane is a B2B marketplace. One checkout becomes several factory orders, and the parts have to add up to the total.";
      if (has(["waraq"])) return "Waraq Oman is an online store. Amwal Pay marks an order paid or failed, and the customer hears about it.";
      if (has(["alejaar"])) return "Alejaar keeps buildings, units, tenants, rent and open maintenance requests in one Laravel application.";
      if (has(["thunder"])) return "A delivery marketplace with customer, store and driver apps, and an order that moves through 13 states. The distinctive piece is ordering an item the store has not priced yet.";
      if (has(["mosaed", "elbhos"])) return "A live platform connecting researchers with consultants, technicians and institutions. About thirteen request workflows and a course platform share one order lifecycle.";
      if (has(["experience accelerator", "accelerator"])) return "A learning platform for leadership practice. It covers behaviour assessment, progress over time, role-based access and feedback workflows.";
      if (has(["data aggregation", "aggregation", "scrapy"])) return "Crawlers collect product pages, an ETL pipeline loads MySQL, and Pusher and Firebase carry live updates and sign-in. Proxy rotation keeps a blocked address from stopping the run.";
      if (has(["billing portal"])) return "An internal portal that creates Stripe payments and keeps a client's monthly bill lined up with the services they are on.";
      if (has(["avatar"])) return "The Avatar Coach is a Laravel application. Retrieval-augmented generation pulls earlier context into the conversation. The app logs what was said, tracks goals, and produces feedback reports from that record.";
      if (has(["business finders", "finders"])) return "A directory with search and filters, listing management for the business, and a moderation queue for the admin.";
      if (has(["ash shop", "perfume"])) return "An Oman storefront for perfume sets: search, a cart, a wishlist, and checkout by card or cash on delivery.";
      if (has(["ashram"])) return "The public site for United Christian Ashrams: events, blogs, podcasts, resources, and donations.";
      if (has(["film"])) return "A film-crew listing. People find available crew, hire them, or follow a job already in progress.";
      if (has(["unique advertisers", "advertisers"])) return "An informational site. Visitors compare packages and leave a lead.";
      if (has(["recruit"])) return "A hiring product with Laravel on the server and React on the client.";
      if (has(["figma", "workflow", "feature pack", "how i ship", "how he ship"])) return "He cuts a Figma file and a business analysis into small feature packs. An agent implements each one under rules that stay fixed. He reviews every change.";
      if (has(["google cloud", "google oauth", "oauth", "google"])) return "Google Cloud and Google OAuth are among the integrations on this site.";
      if (has(["skill", "stack", "laravel", "php", "mysql"])) return "PHP, Laravel and MySQL, plus REST APIs, payments, queues and tests. More than five years with them, over four of those professionally.";
      if (has(["project", "case", "built", "portfolio"])) return "The homepage case studies are VALORA, Food Link, Msane, Waraq Oman and Alejaar. The work page has the rest.";
      if (has(["thiqatech", "techigator", "olive", "company", "job history"])) return "Senior backend developer at Thiqatech, remote with a team in Muscat. Before that, Techigator and Olive Digital. Star of the Quarter at Techigator in 2024.";
      if (has(["who", "about", "experience", "team", "years"])) return "Muhammad Adil is a senior backend developer. On recent projects he was the only backend developer. Before that he led a backend team of up to 4.";
      return "That is not written on this site, so I will not guess. Send it on WhatsApp and Muhammad will read it.";
    }
    var turns = [];
    var busy = false;
    if (form) {
      form.addEventListener("submit", function (event) {
        event.preventDefault();
        var text = field.value.trim();
        if (!text || busy || !contactReady()) return;
        if (!greeted) greeted = true;
        form.classList.add("is-chatting");
        addLine(text, "from-user");
        field.value = "";
        if (field) field.focus();
        if (handoff) handoff.href = "https://api.whatsapp.com/send/?phone=923180253146&text=" + encodeURIComponent(text) + "&type=phone_number&app_absent=0";
        turns.push({ role: "user", text: text });
        if (turns.length > 8) turns = turns.slice(-8);
        busy = true;
        var pending = addTyping();
        var chatBody = {
          messages: turns,
          lang: root.dataset.lang || "en",
          sessionId: ensureSession(),
          path: location.pathname,
          contact: {
            name: nameField ? nameField.value.trim() : "",
            phone: phoneField ? phoneField.value.trim() : "",
            email: emailField ? emailField.value.trim() : ""
          }
        };
        if (readCookie("adil-consent") === "all") chatBody.context = visitContext();
        fetch("/api/chat", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(chatBody)
        }).then(function (response) {
          if (!response.ok) throw new Error("unavailable");
          return response.json();
        }).then(function (data) {
          if (!data || !data.reply) throw new Error("empty");
          setLine(pending, data.reply);
          turns.push({ role: "model", text: data.reply });
        }).catch(function () {
          var fallback = t(answer(text));
          setLine(pending, fallback);
          turns.push({ role: "model", text: fallback });
          fetch("/api/chat", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              saveReply: true,
              userText: text,
              reply: fallback,
              sessionId: chatBody.sessionId,
              path: chatBody.path,
              contact: chatBody.contact,
              context: chatBody.context
            })
          }).catch(function () {});
        }).then(function () {
          busy = false;
          log.scrollTop = log.scrollHeight;
        });
      });
    }
  }

  if (root.dataset.lang) applyLang(root.dataset.lang);

  if (!reduce) {
    var nodes = document.querySelectorAll(".case-list > li, .card, .principles article, .page-head, .job, .section-head");
    var waiting = [];
    nodes.forEach(function (node, index) {
      node.classList.add("reveal");
      node.style.animationDelay = (index % 5) * 45 + "ms";
      waiting.push(node);
    });
    var revealTick = false;
    function revealVisible() {
      revealTick = false;
      if (!waiting.length) return;
      var edge = window.innerHeight * 0.96;
      waiting = waiting.filter(function (node) {
        var rect = node.getBoundingClientRect();
        if (rect.top < edge && rect.bottom > 0) {
          node.classList.add("is-in");
          return false;
        }
        return true;
      });
    }
    function queueReveal() {
      if (revealTick) return;
      revealTick = true;
      requestAnimationFrame(revealVisible);
    }
    queueReveal();
    window.addEventListener("scroll", queueReveal, { passive: true });
    window.addEventListener("resize", queueReveal);
  }

  function readCookie(name) {
    var parts = document.cookie ? document.cookie.split("; ") : [];
    for (var i = 0; i < parts.length; i++) {
      var pair = parts[i].split("=");
      if (pair[0] === name) return decodeURIComponent(pair.slice(1).join("="));
    }
    return "";
  }
  function writeCookie(name, value) {
    document.cookie = name + "=" + encodeURIComponent(value) + "; Path=/; Max-Age=15552000; SameSite=Lax";
  }
  function clearCookie(name) {
    document.cookie = name + "=; Path=/; Max-Age=0; SameSite=Lax";
  }
  function rememberPrefs() {
    if (readCookie("adil-consent") !== "all") return;
    writeCookie("adil-theme", currentTheme());
    writeCookie("adil-lang", root.dataset.lang || "en");
  }
  var cookieBar = document.querySelector("#cookie-bar");
  var cookieModal = document.querySelector("#cookie-modal");
  var cookieRemember = document.querySelector("#cookie-remember");
  if (cookieBar && !readCookie("adil-consent")) root.classList.remove("cookies-set");
  function randomId() {
    if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
    return "v-" + Math.random().toString(36).slice(2) + Date.now().toString(36);
  }
  function deviceLabel() {
    var ua = navigator.userAgent || "";
    var kind = /iPad|Tablet/i.test(ua) ? "tablet" : /Mobi|Android|iPhone/i.test(ua) ? "phone" : "desktop";
    var browser = /Edg\//.test(ua) ? "Edge" : /Chrome\//.test(ua) ? "Chrome" : /Safari\//.test(ua) && !/Chrome\//.test(ua) ? "Safari" : /Firefox\//.test(ua) ? "Firefox" : "browser";
    return kind + ", " + browser;
  }
  function ensureSession() {
    var id = readCookie("adil-session");
    if (id) return id;
    id = randomId();
    document.cookie = "adil-session=" + encodeURIComponent(id) + "; Path=/; SameSite=Lax";
    return id;
  }
  function ensureVisitor() {
    var id = readCookie("adil-id");
    if (id) return id;
    id = randomId();
    writeCookie("adil-id", id);
    return id;
  }
  function visitContext() {
    var region = "";
    var referrer = "";
    var landing = "";
    try {
      region = sessionStorage.getItem("adil-region") || "";
      referrer = sessionStorage.getItem("adil-referrer") || "";
      landing = sessionStorage.getItem("adil-landing") || "";
    } catch (error) {}
    return {
      visitorId: readCookie("adil-id"),
      region: region,
      referrer: referrer,
      landing: landing,
      device: deviceLabel()
    };
  }
  function recordVisit() {
    var landing = location.pathname;
    var referrer = "direct";
    try {
      if (!sessionStorage.getItem("adil-landing")) sessionStorage.setItem("adil-landing", location.pathname);
      landing = sessionStorage.getItem("adil-landing") || landing;
      if (!sessionStorage.getItem("adil-referrer")) sessionStorage.setItem("adil-referrer", document.referrer || "direct");
      referrer = sessionStorage.getItem("adil-referrer") || referrer;
    } catch (error) {}
    fetch("/api/visit", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      keepalive: true,
      body: JSON.stringify({
        path: location.pathname,
        device: deviceLabel(),
        referrer: referrer,
        landing: landing,
        sessionId: ensureSession(),
        visitorId: readCookie("adil-id") || ""
      })
    }).then(function (response) {
      return response.ok ? response.json() : null;
    }).then(function (data) {
      if (data && data.region) {
        try { sessionStorage.setItem("adil-region", data.region); } catch (error) {}
      }
    }).catch(function () {});
  }
  function applyConsent(choice) {
    var all = choice === "all";
    writeCookie("adil-consent", all ? "all" : "necessary");
    if (all) {
      rememberPrefs();
      ensureVisitor();
    } else {
      clearCookie("adil-theme");
      clearCookie("adil-lang");
      clearCookie("adil-id");
    }
    root.classList.add("cookies-set");
    root.classList.remove("cookie-open");
    if (cookieModal) cookieModal.hidden = true;
  }
  function openCookieModal() {
    if (!cookieModal) return;
    if (cookieRemember) cookieRemember.setAttribute("aria-checked", readCookie("adil-consent") === "all" ? "true" : "false");
    cookieModal.hidden = false;
    root.classList.add("cookie-open");
  }
  if (cookieBar) {
    cookieBar.addEventListener("click", function (event) {
      if (event.target.closest("[data-cookie-manage]")) {
        openCookieModal();
        return;
      }
      var button = event.target.closest("[data-cookie]");
      if (!button) return;
      applyConsent(button.getAttribute("data-cookie") === "all" ? "all" : "necessary");
    });
  }
  if (cookieModal) {
    cookieModal.addEventListener("click", function (event) {
      if (event.target === cookieModal || event.target.closest("[data-cookie-close]")) {
        cookieModal.hidden = true;
        root.classList.remove("cookie-open");
        return;
      }
      if (event.target.closest(".cookie-switch")) {
        var on = cookieRemember.getAttribute("aria-checked") === "true";
        cookieRemember.setAttribute("aria-checked", on ? "false" : "true");
        return;
      }
      var button = event.target.closest("[data-cookie]");
      if (!button) return;
      var kind = button.getAttribute("data-cookie");
      if (kind === "save") kind = cookieRemember && cookieRemember.getAttribute("aria-checked") === "true" ? "all" : "necessary";
      if (kind === "all" && cookieRemember) cookieRemember.setAttribute("aria-checked", "true");
      if (kind === "necessary" && cookieRemember) cookieRemember.setAttribute("aria-checked", "false");
      applyConsent(kind === "all" ? "all" : "necessary");
    });
    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && !cookieModal.hidden) {
        cookieModal.hidden = true;
        root.classList.remove("cookie-open");
      }
    });
  }
  recordVisit();

  function localSite() {
    var host = location.hostname;
    return host === "localhost" || host === "127.0.0.1" || host === "::1";
  }
  var humanKey = "adil-human";
  var humanFor = 24 * 60 * 60 * 1000;
  var gate = document.querySelector("#captcha-gate");
  function passedBefore() {
    try {
      var at = Number(localStorage.getItem(humanKey));
      return at > 0 && Date.now() - at < humanFor;
    } catch (error) { return false; }
  }
  function markPassed() {
    try { localStorage.setItem(humanKey, String(Date.now())); } catch (error) {}
  }
  function showGate(open) {
    if (!gate) return;
    gate.hidden = !open;
    Array.prototype.forEach.call(document.body.children, function (node) {
      if (node === gate) return;
      if (open) node.setAttribute("inert", "");
      else node.removeAttribute("inert");
    });
  }
  if (gate) {
    gate.addEventListener("wheel", function (event) {
      if (!gate.hidden) event.preventDefault();
    }, { passive: false });
    gate.addEventListener("touchmove", function (event) {
      if (!gate.hidden) event.preventDefault();
    }, { passive: false });
  }
  function setupCaptcha() {
    if (!gate || localSite() || passedBefore()) return;
    fetch("/api/captcha").then(function (response) {
      if (!response.ok) throw new Error("unavailable");
      return response.json();
    }).then(function (data) {
      if (!data || !data.enabled || !data.siteKey) return;
      showGate(true);
      var fail = gate.querySelector(".captcha-error");
      window.adilCaptchaReady = function () {
        if (!window.grecaptcha || gate.dataset.ready === "1") return;
        gate.dataset.ready = "1";
        window.grecaptcha.render("captcha-widget", {
          sitekey: data.siteKey,
          theme: root.dataset.theme === "light" ? "light" : "dark",
          callback: function (token) {
            fetch("/api/captcha", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ token: token })
            }).then(function (response) {
              if (!response.ok) throw new Error("rejected");
              return response.json();
            }).then(function (result) {
              if (!result || !result.ok) throw new Error("rejected");
              markPassed();
              showGate(false);
            }).catch(function () {
              if (fail) fail.hidden = false;
              if (window.grecaptcha) window.grecaptcha.reset();
            });
          }
        });
      };
      var script = document.createElement("script");
      script.src = "https://www.google.com/recaptcha/api.js?onload=adilCaptchaReady&render=explicit";
      script.async = true;
      document.head.appendChild(script);
    }).catch(function () {});
  }
  setupCaptcha();

  var dialog = document.querySelector("#cv-preview");
  if (!dialog) return;

  document.addEventListener("click", function (event) {
    var target = event.target;
    if (!(target instanceof Element)) return;
    if (target.closest("[data-cv-preview]")) {
      dialog.showModal();
    } else if (target === dialog || target.closest("[data-cv-close]")) {
      dialog.close();
    }
  });
})();
