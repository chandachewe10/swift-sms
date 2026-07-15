
  <!-- ======= Header ======= -->
  <style>
    #swift-header {
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 9999;
      padding: 14px 0;
      background: #fff;
      box-shadow: 0 2px 20px rgba(0,0,0,0.10);
      transform: translateY(-100%);
      opacity: 0;
      transition: transform 0.35s ease, opacity 0.35s ease;
    }
    #swift-header.visible {
      transform: translateY(0);
      opacity: 1;
    }
    #swift-header nav a {
      color: #374151;
      font-size: 13px;
      font-weight: 600;
      text-decoration: none;
      letter-spacing: 0.04em;
      transition: color 0.2s;
    }
    #swift-header nav a:hover { color: #F9A826; }
    #swift-header .btn-started {
      background: #F9A826;
      color: #0a0f1e !important;
      padding: 8px 20px;
      border-radius: 8px;
      font-weight: 700 !important;
    }

    /* Hamburger button */
    #mobile-menu-btn {
      display: none;
      flex-direction: column;
      justify-content: center;
      gap: 5px;
      width: 38px;
      height: 38px;
      cursor: pointer;
      background: none;
      border: none;
      padding: 4px;
    }
    #mobile-menu-btn span {
      display: block;
      height: 2px;
      width: 100%;
      background: #0a0f1e;
      border-radius: 2px;
      transition: all 0.3s ease;
    }
    #mobile-menu-btn.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
    #mobile-menu-btn.open span:nth-child(2) { opacity: 0; }
    #mobile-menu-btn.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

    @media (max-width: 991px) {
      #mobile-menu-btn { display: flex; }
    }

    /* Mobile drawer overlay */
    #mobile-drawer {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      z-index: 9998;
      display: flex;
      pointer-events: none;
    }
    #mobile-drawer-overlay {
      position: absolute;
      inset: 0;
      background: rgba(0,0,0,0.45);
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    #mobile-drawer-panel {
      position: absolute;
      top: 0; right: 0;
      width: min(80vw, 300px);
      height: 100%;
      background: #fff;
      box-shadow: -4px 0 30px rgba(0,0,0,0.15);
      transform: translateX(100%);
      transition: transform 0.35s cubic-bezier(.4,0,.2,1);
      display: flex;
      flex-direction: column;
      padding: 28px 24px;
      overflow-y: auto;
    }
    #mobile-drawer.open {
      pointer-events: all;
    }
    #mobile-drawer.open #mobile-drawer-overlay { opacity: 1; }
    #mobile-drawer.open #mobile-drawer-panel   { transform: translateX(0); }

    #mobile-drawer-panel .drawer-logo {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 32px;
      padding-bottom: 20px;
      border-bottom: 1px solid #f1f5f9;
    }
    #mobile-drawer-panel a.drawer-link {
      display: block;
      color: #374151;
      font-size: 15px;
      font-weight: 600;
      text-decoration: none;
      padding: 13px 0;
      border-bottom: 1px solid #f1f5f9;
      letter-spacing: 0.04em;
      transition: color 0.2s;
    }
    #mobile-drawer-panel a.drawer-link:hover { color: #F9A826; }
    #mobile-drawer-panel .drawer-cta {
      display: block;
      margin-top: 24px;
      background: #F9A826;
      color: #0a0f1e !important;
      font-weight: 800;
      font-size: 15px;
      padding: 14px;
      border-radius: 12px;
      text-align: center;
      text-decoration: none;
    }
  </style>

  <header id="swift-header">
    <div class="container d-flex align-items-center justify-content-between">
      <a href="/" style="text-decoration:none;display:flex;align-items:center;gap:10px;">
        <img src="{{asset('landing-page/img/logo/logo.jpg')}}" alt="SwiftSMS" style="height:36px;border-radius:50%;">
        <span style="font-weight:800;font-size:18px;color:#0a0f1e;">SwiftSMS</span>
      </a>

      <!-- Desktop nav -->
      <nav class="d-none d-lg-flex align-items-center" style="gap:28px;">
        <a href="#why-us">ABOUT</a>
        <a href="#services">SERVICES</a>
        <a href="#pricing">PRICING</a>
        <a href="{{route('api_docs')}}">API</a>
        <a href="#footer">CONTACT</a>
        <a href="/admin/login">LOGIN</a>
        <a href="/admin/register" class="btn-started">GET STARTED</a>
      </nav>

      <!-- Mobile hamburger -->
      <button id="mobile-menu-btn" aria-label="Open menu" onclick="toggleDrawer()">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>

  <!-- Mobile drawer -->
  <div id="mobile-drawer">
    <div id="mobile-drawer-overlay" onclick="toggleDrawer()"></div>
    <div id="mobile-drawer-panel">
      <div class="drawer-logo">
        <img src="{{asset('landing-page/img/logo/logo.jpg')}}" alt="SwiftSMS" style="height:34px;border-radius:50%;">
        <span style="font-weight:800;font-size:17px;color:#0a0f1e;">SwiftSMS</span>
      </div>
      <a href="#why-us"      class="drawer-link" onclick="toggleDrawer()">About</a>
      <a href="#services"    class="drawer-link" onclick="toggleDrawer()">Services</a>
      <a href="#pricing"     class="drawer-link" onclick="toggleDrawer()">Pricing</a>
      <a href="{{route('api_docs')}}" class="drawer-link" onclick="toggleDrawer()">API Docs</a>
      <a href="#footer"      class="drawer-link" onclick="toggleDrawer()">Contact</a>
      <a href="/admin/login" class="drawer-link" onclick="toggleDrawer()">Login</a>
      <a href="/admin/register" class="drawer-cta">Get Started &#8594;</a>
    </div>
  </div>

  <script>
    (function(){
      var header  = document.getElementById('swift-header');
      var drawer  = document.getElementById('mobile-drawer');
      var menuBtn = document.getElementById('mobile-menu-btn');

      // Show header on scroll
      window.addEventListener('scroll', function(){
        if (window.scrollY > 80) {
          header.classList.add('visible');
        } else {
          header.classList.remove('visible');
        }
      });

      window.toggleDrawer = function() {
        var isOpen = drawer.classList.toggle('open');
        menuBtn.classList.toggle('open', isOpen);
        document.body.style.overflow = isOpen ? 'hidden' : '';
      };
    })();
  </script>
  <!-- End Header -->


  <!-- ======= Hero Section ======= -->
  <section id="hero" style="background:linear-gradient(135deg,#0a0f1e 0%,#0d1b3e 60%,#0a2a5e 100%);min-height:100vh;display:flex;align-items:center;padding:120px 0 80px;">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6" data-aos="fade-right">
          <div style="margin-bottom:16px;">
            <span style="background:rgba(249,168,38,0.15);color:#F9A826;border:1px solid rgba(249,168,38,0.3);padding:6px 16px;border-radius:20px;font-size:13px;font-weight:600;">&#127758; Zambia &amp; International SMS</span>
          </div>
          <h1 style="font-size:clamp(36px,5vw,58px);font-weight:900;color:#fff;line-height:1.15;margin-bottom:20px;">
            Reach Thousands<br><span style="color:#F9A826;">Instantly</span> with<br>Bulk SMS
          </h1>
          <p style="font-size:17px;color:#94a3b8;line-height:1.8;max-width:500px;margin-bottom:32px;">
            SwiftSMS connects you directly to MTN, Airtel &amp; Zamtel — plus international numbers worldwide. Send SMS, WhatsApp messages, and Emails all from one powerful platform.
          </p>
          <div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:28px;">
            <a href="/admin/register" style="background:#F9A826;color:#0a0f1e;font-weight:800;font-size:15px;padding:14px 32px;border-radius:12px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">&#128640; Start Free Trial</a>
            <a href="#pricing" style="background:rgba(255,255,255,0.08);color:#fff;border:1px solid rgba(255,255,255,0.2);font-weight:600;font-size:15px;padding:14px 32px;border-radius:12px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;backdrop-filter:blur(6px);">See Pricing &#8595;</a>
          </div>
          <div style="display:flex;gap:24px;flex-wrap:wrap;">
            <span style="color:#64748b;font-size:13px;">&#9989; No setup fees</span>
            <span style="color:#64748b;font-size:13px;">&#9989; Pay as you go</span>
            <span style="color:#64748b;font-size:13px;">&#9989; Instant activation</span>
          </div>
        </div>
        <div class="col-lg-6 d-none d-lg-flex justify-content-center" data-aos="fade-left">
          <div style="position:relative;width:100%;max-width:480px;">
            <!-- Phone mockup with messages -->
            <div style="background:linear-gradient(135deg,#1e293b,#0f172a);border-radius:24px;padding:32px;border:1px solid rgba(255,255,255,0.08);box-shadow:0 40px 80px rgba(0,0,0,0.5);">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid rgba(255,255,255,0.08);">
                <div style="width:10px;height:10px;border-radius:50%;background:#22c55e;box-shadow:0 0 8px #22c55e;"></div>
                <span style="color:#94a3b8;font-size:13px;font-weight:600;">SwiftSMS Dashboard — Live</span>
              </div>
              <div style="space-y:12px;">
                <div style="background:rgba(249,168,38,0.1);border:1px solid rgba(249,168,38,0.2);border-radius:12px;padding:14px;margin-bottom:12px;">
                  <div style="font-size:11px;color:#F9A826;font-weight:700;margin-bottom:4px;">&#128232; BULK SMS — SENT</div>
                  <div style="color:#f1f5f9;font-size:14px;">"Your OTP code is 847291. Valid for 5 minutes. Do not share this code with anyone."</div>
                  <div style="font-size:11px;color:#64748b;margin-top:8px;">&#10003;&#10003; Delivered to 1,247 contacts &bull; MTN, Airtel, Zamtel</div>
                </div>
                <div style="background:rgba(37,211,102,0.1);border:1px solid rgba(37,211,102,0.2);border-radius:12px;padding:14px;margin-bottom:12px;">
                  <div style="font-size:11px;color:#25D366;font-weight:700;margin-bottom:4px;">&#128255; WHATSAPP — DELIVERED</div>
                  <div style="color:#f1f5f9;font-size:14px;">"Hi @{{name}}, your appointment is tomorrow at 10AM. Reply YES to confirm."</div>
                  <div style="font-size:11px;color:#64748b;margin-top:8px;">&#10003;&#10003; Read by 523 / 600 recipients</div>
                </div>
                <div style="background:rgba(66,133,244,0.1);border:1px solid rgba(66,133,244,0.2);border-radius:12px;padding:14px;">
                  <div style="font-size:11px;color:#4285F4;font-weight:700;margin-bottom:4px;">&#128140; BULK EMAIL — FREE</div>
                  <div style="color:#f1f5f9;font-size:14px;">"Monthly newsletter sent to all 3,200 subscribers successfully."</div>
                  <div style="font-size:11px;color:#64748b;margin-top:8px;">&#10003; 98.2% delivery rate &bull; 0 bounces</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section><!-- End Hero -->

  <!-- ======= Stats Bar ======= -->
  <section style="background:#F9A826;padding:28px 0;">
    <div class="container">
      <div class="row text-center" style="align-items:center;">
        <div class="col-6 col-md-3" style="padding:12px 0;">
          <div style="font-size:32px;font-weight:900;color:#0a0f1e;line-height:1;">3</div>
          <div style="font-size:13px;font-weight:600;color:#0a0f1e;opacity:0.75;margin-top:4px;">Local Networks Covered</div>
        </div>
        <div class="col-6 col-md-3" style="padding:12px 0;border-left:1px solid rgba(0,0,0,0.15);">
          <div style="font-size:32px;font-weight:900;color:#0a0f1e;line-height:1;">~98%</div>
          <div style="font-size:13px;font-weight:600;color:#0a0f1e;opacity:0.75;margin-top:4px;">Average SMS Open Rate</div>
        </div>
        <div class="col-6 col-md-3" style="padding:12px 0;border-left:1px solid rgba(0,0,0,0.15);">
          <div style="font-size:32px;font-weight:900;color:#0a0f1e;line-height:1;">K0.17</div>
          <div style="font-size:13px;font-weight:600;color:#0a0f1e;opacity:0.75;margin-top:4px;">Lowest Cost per SMS</div>
        </div>
        <div class="col-6 col-md-3" style="padding:12px 0;border-left:1px solid rgba(0,0,0,0.15);">
          <div style="font-size:32px;font-weight:900;color:#0a0f1e;line-height:1;">3-in-1</div>
          <div style="font-size:13px;font-weight:600;color:#0a0f1e;opacity:0.75;margin-top:4px;">SMS · WhatsApp · Email</div>
        </div>
      </div>
    </div>
  </section>

  <main id="main">

  <!-- ======= Why Choose Us ======= -->
  <section id="why-us" style="padding:80px 0;background:#fff;">
    <div class="container" data-aos="fade-up">
      <div class="row align-items-center">
        <div class="col-lg-5" style="margin-bottom:30px;">
          <span style="background:#fff8ec;color:#F9A826;border:1px solid #fde68a;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;">Why SwiftSMS</span>
          <h2 style="font-size:clamp(26px,4vw,40px);font-weight:800;color:#0a0f1e;line-height:1.3;margin:16px 0 20px;">The Smarter Way to<br>Communicate at Scale</h2>
          <p style="color:#64748b;font-size:15px;line-height:1.8;margin-bottom:28px;">Built for Zambian businesses, designed for the world. Whether you're sending 100 or 100,000 messages, SwiftSMS delivers reliably every time.</p>
          <a href="/admin/register" style="background:#0a0f1e;color:#F9A826;font-weight:700;font-size:14px;padding:13px 28px;border-radius:10px;text-decoration:none;display:inline-block;">Get Started Free &#8594;</a>
        </div>
        <div class="col-lg-7">
          <div class="row">
            @php $features = [
              ['icon'=>'&#128246;','color'=>'#fff8ec','border'=>'#fde68a','title'=>'Multi-Network Coverage','desc'=>'Reach MTN, Airtel & Zamtel subscribers instantly. International numbers supported with automatic country code handling.'],
              ['icon'=>'&#9889;','color'=>'#eff6ff','border'=>'#bfdbfe','title'=>'Flash & Scheduled SMS','desc'=>'Send urgent flash messages that display immediately, or schedule campaigns for the perfect moment.'],
              ['icon'=>'&#128202;','color'=>'#f0fdf4','border'=>'#bbf7d0','title'=>'Real-Time Delivery Reports','desc'=>'Track every message. Know exactly who received your SMS, at what time, and on which network.'],
              ['icon'=>'&#128279;','color'=>'#fdf4ff','border'=>'#e9d5ff','title'=>'Powerful REST API','desc'=>'Integrate SMS sending into your app, website or CRM in minutes. Full API docs included.'],
              ['icon'=>'&#128101;','color'=>'#fef9c3','border'=>'#fde68a','title'=>'Contact Management','desc'=>'Import contacts via CSV, tag them, and filter sends by group or tag. Bulk send to thousands with one click.'],
              ['icon'=>'&#128274;','color'=>'#fee2e2','border'=>'#fca5a5','title'=>'Secure & Reliable','desc'=>'HTTPS/SSL encrypted. Your data and your customers data stays safe. 99.9% platform uptime.'],
            ]; @endphp
            @foreach($features as $f)
            <div class="col-sm-6" style="padding:10px;">
              <div style="background:{{ $f['color'] }};border:1px solid {{ $f['border'] }};border-radius:14px;padding:20px;height:100%;transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="font-size:28px;margin-bottom:10px;">{!! $f['icon'] !!}</div>
                <h4 style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:6px;">{{ $f['title'] }}</h4>
                <p style="font-size:13px;color:#64748b;margin:0;line-height:1.6;">{{ $f['desc'] }}</p>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ======= Services Section ======= -->
  <section id="services" style="padding:80px 0;background:#f8fafc;">
    <div class="container" data-aos="fade-up">
      <div style="text-align:center;margin-bottom:48px;">
        <span style="background:#fff8ec;color:#F9A826;border:1px solid #fde68a;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;">Our Channels</span>
        <h2 style="font-size:clamp(24px,4vw,38px);font-weight:800;color:#0a0f1e;margin:16px 0 10px;">One Platform, Three Channels</h2>
        <p style="color:#64748b;font-size:15px;max-width:500px;margin:0 auto;">Everything you need to reach your audience — in the channel they prefer.</p>
      </div>

      <div class="row">
        <!-- SMS -->
        <div class="col-md-4" style="padding:12px;" data-aos="fade-up">
          <div style="background:#fff;border-radius:20px;padding:36px 28px;height:100%;box-shadow:0 4px 24px rgba(0,0,0,0.07);border-top:4px solid #F9A826;transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-6px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="width:56px;height:56px;background:#fff8ec;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:28px;margin-bottom:20px;">&#128232;</div>
            <h3 style="font-size:20px;font-weight:800;color:#0a0f1e;margin-bottom:10px;">Bulk SMS</h3>
            <p style="color:#64748b;font-size:14px;line-height:1.7;margin-bottom:20px;">Send thousands of SMS to local and international numbers instantly. Supports flash SMS, scheduled sends, CSV import, tag-based filtering, and full delivery tracking.</p>
            <ul style="list-style:none;padding:0;font-size:13px;color:#374151;">
              <li style="padding:4px 0;">&#10003; MTN, Airtel &amp; Zamtel</li>
              <li style="padding:4px 0;">&#10003; International numbers</li>
              <li style="padding:4px 0;">&#10003; Flash &amp; scheduled SMS</li>
              <li style="padding:4px 0;">&#10003; From K0.17/SMS</li>
            </ul>
            <a href="#pricing" style="display:inline-block;margin-top:20px;color:#F9A826;font-weight:700;font-size:14px;text-decoration:none;">View Pricing &#8594;</a>
          </div>
        </div>

        <!-- WhatsApp -->
        <div class="col-md-4" style="padding:12px;" data-aos="fade-up">
          <div style="background:#fff;border-radius:20px;padding:36px 28px;height:100%;box-shadow:0 4px 24px rgba(0,0,0,0.07);border-top:4px solid #25D366;transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-6px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="width:56px;height:56px;background:#f0fdf4;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:28px;margin-bottom:20px;">
              <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="#25D366"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </div>
            <h3 style="font-size:20px;font-weight:800;color:#0a0f1e;margin-bottom:10px;">WhatsApp Business <span style="background:#fff3cd;color:#856404;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;vertical-align:middle;">NEW</span></h3>
            <p style="color:#64748b;font-size:14px;line-height:1.7;margin-bottom:20px;">Send approved template messages to your customers via Meta's WhatsApp Cloud API. Higher open rates, richer content, and direct engagement — all managed from your dashboard.</p>
            <ul style="list-style:none;padding:0;font-size:13px;color:#374151;">
              <li style="padding:4px 0;">&#10003; Meta WhatsApp Cloud API</li>
              <li style="padding:4px 0;">&#10003; Approved message templates</li>
              <li style="padding:4px 0;">&#10003; Bulk multi-recipient sends</li>
              <li style="padding:4px 0;">&#10003; K500/month + 10 free sends</li>
            </ul>
            <div style="margin-top:16px;padding:10px 14px;border-radius:10px;background:#fffbeb;border:1px solid #fde68a;font-size:12px;color:#6b7280;">
              <div style="font-weight:700;color:#b45309;margin-bottom:4px;">How Billing Works</div>
              <p style="margin:0 0 4px;"><strong style="color:#374151;">K500/month</strong> goes to SwiftSMS — covers API access, system, contact management &amp; embedded signup.</p>
              <p style="margin:0 0 8px;">Message costs are billed <strong style="color:#374151;">directly by Meta</strong>. Zambia rates:</p>
              <table style="width:100%;font-size:11px;border-collapse:collapse;">
                <tr style="border-bottom:1px solid #fde68a;"><th style="padding:2px 0;text-align:left;color:#6b7280;">Category</th><th style="padding:2px 0;text-align:right;color:#6b7280;">USD/msg</th></tr>
                <tr style="border-bottom:1px solid #fef3c7;"><td style="padding:3px 0;">Marketing</td><td style="padding:3px 0;text-align:right;font-weight:700;color:#374151;">$0.0225</td></tr>
                <tr style="border-bottom:1px solid #fef3c7;"><td style="padding:3px 0;">Utility</td><td style="padding:3px 0;text-align:right;font-weight:700;color:#374151;">$0.0040</td></tr>
                <tr><td style="padding:3px 0;">Authentication</td><td style="padding:3px 0;text-align:right;font-weight:700;color:#374151;">$0.0040</td></tr>
              </table>
            </div>
            <a href="/admin/register" style="display:inline-block;margin-top:16px;color:#25D366;font-weight:700;font-size:14px;text-decoration:none;">Activate WhatsApp &#8594;</a>
          </div>
        </div>

        <!-- Email -->
        <div class="col-md-4" style="padding:12px;" data-aos="fade-up">
          <div style="background:#fff;border-radius:20px;padding:36px 28px;height:100%;box-shadow:0 4px 24px rgba(0,0,0,0.07);border-top:4px solid #4285F4;transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-6px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="width:56px;height:56px;background:#eff6ff;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:28px;margin-bottom:20px;">&#128140;</div>
            <h3 style="font-size:20px;font-weight:800;color:#0a0f1e;margin-bottom:10px;">Bulk Email</h3>
            <p style="color:#64748b;font-size:14px;line-height:1.7;margin-bottom:20px;">Send single or bulk emails using your own SMTP server — Gmail, Zoho, Outlook and more. Compose rich HTML emails and send to your entire contact list in one click.</p>
            <ul style="list-style:none;padding:0;font-size:13px;color:#374151;">
              <li style="padding:4px 0;">&#10003; Your own SMTP (Gmail, Zoho…)</li>
              <li style="padding:4px 0;">&#10003; Single &amp; bulk sends</li>
              <li style="padding:4px 0;">&#10003; Full delivery logs</li>
              <li style="padding:4px 0;">&#10003; K500/month + 10 free sends</li>
            </ul>
            <a href="/admin/register" style="display:inline-block;margin-top:20px;color:#4285F4;font-weight:700;font-size:14px;text-decoration:none;">Activate Bulk Email &#8594;</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ======= How It Works ======= -->
  <section style="padding:80px 0;background:#0a0f1e;">
    <div class="container" data-aos="fade-up">
      <div style="text-align:center;margin-bottom:48px;">
        <span style="background:rgba(249,168,38,0.15);color:#F9A826;border:1px solid rgba(249,168,38,0.3);padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;">Simple Process</span>
        <h2 style="font-size:clamp(24px,4vw,38px);font-weight:800;color:#f1f5f9;margin:16px 0 10px;">Send Your First SMS in 3 Steps</h2>
        <p style="color:#64748b;font-size:15px;max-width:480px;margin:0 auto;">No technical knowledge required. Start sending in under 5 minutes.</p>
      </div>
      <div class="row justify-content-center">
        @php $steps = [
          ['num'=>'01','icon'=>'&#128100;','title'=>'Create Your Account','desc'=>'Sign up free in under a minute. No credit card required. Your account is activated instantly.','color'=>'#F9A826'],
          ['num'=>'02','icon'=>'&#128179;','title'=>'Top Up SMS Credits','desc'=>'Choose a bundle that fits your needs — from K340 for 1,000 SMS up to K17,000 for 100,000 SMS. Credits never expire.','color'=>'#22c55e'],
          ['num'=>'03','icon'=>'&#128232;','title'=>'Send & Track','desc'=>'Upload contacts or type numbers manually. Hit send and watch real-time delivery reports roll in.','color'=>'#4285F4'],
        ]; @endphp
        @foreach($steps as $i => $s)
        <div class="col-md-4" style="padding:12px;text-align:center;" data-aos="fade-up">
          <div style="position:relative;background:linear-gradient(135deg,#1e293b,#0f172a);border:1px solid rgba(255,255,255,0.06);border-radius:20px;padding:36px 24px;">
            <div style="position:absolute;top:-18px;left:50%;transform:translateX(-50%);background:{{ $s['color'] }};color:#0a0f1e;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:13px;">{{ $s['num'] }}</div>
            <div style="font-size:42px;margin:16px 0 16px;">{!! $s['icon'] !!}</div>
            <h4 style="font-size:17px;font-weight:800;color:#f1f5f9;margin-bottom:10px;">{{ $s['title'] }}</h4>
            <p style="font-size:13px;color:#64748b;line-height:1.7;margin:0;">{{ $s['desc'] }}</p>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </section>

  <!-- ======= SMS Pricing Section ======= -->
  <section id="pricing" style="padding:80px 0;background:#f8fafc;">
    <div class="container" data-aos="fade-up">

      <div style="text-align:center;margin-bottom:16px;">
        <span style="background:#fff8ec;color:#F9A826;border:1px solid #fde68a;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;">Pricing</span>
        <h2 style="font-size:clamp(24px,4vw,38px);font-weight:800;color:#0a0f1e;margin:16px 0 10px;">Simple, Transparent Pricing</h2>
        <p style="color:#64748b;font-size:15px;max-width:520px;margin:0 auto 32px;">The more you send, the less you pay per SMS. Credits never expire — use them at your own pace.</p>
      </div>

      {{-- Tab toggle --}}
      <div style="display:flex;justify-content:center;margin-bottom:40px;">
        <div style="background:#e2e8f0;border-radius:30px;padding:4px;display:inline-flex;gap:4px;">
          <button onclick="document.getElementById('local-tab').style.display='block';document.getElementById('intl-tab').style.display='none';this.style.background='#fff';this.style.boxShadow='0 1px 4px rgba(0,0,0,0.1)';document.getElementById('btn-intl').style.background='transparent';document.getElementById('btn-intl').style.boxShadow='none';"
            id="btn-local"
            style="background:#fff;border:none;padding:10px 28px;border-radius:26px;font-weight:700;font-size:14px;cursor:pointer;box-shadow:0 1px 4px rgba(0,0,0,0.1);color:#0f172a;">
            &#127481;&#127487; Local SMS
          </button>
          <button onclick="document.getElementById('intl-tab').style.display='block';document.getElementById('local-tab').style.display='none';this.style.background='#fff';this.style.boxShadow='0 1px 4px rgba(0,0,0,0.1)';document.getElementById('btn-local').style.background='transparent';document.getElementById('btn-local').style.boxShadow='none';"
            id="btn-intl"
            style="background:transparent;border:none;padding:10px 28px;border-radius:26px;font-weight:700;font-size:14px;cursor:pointer;color:#64748b;">
            &#127758; International
          </button>
        </div>
      </div>

      {{-- LOCAL TAB --}}
      <div id="local-tab">
        @php
        $smsBundles = [
            ['price'=>'K340',    'raw'=>340,   'sms'=>'1,000',   'per'=>'K0.34/SMS', 'popular'=>false, 'save'=>null,          'label'=>'Starter'],
            ['price'=>'K1,340',  'raw'=>1340,  'sms'=>'5,000',   'per'=>'K0.27/SMS', 'popular'=>false, 'save'=>'Save K360',   'label'=>'Bronze'],
            ['price'=>'K2,000',  'raw'=>2000,  'sms'=>'9,000',   'per'=>'K0.22/SMS', 'popular'=>false, 'save'=>'Save K1,060', 'label'=>'Silver'],
            ['price'=>'K4,750',  'raw'=>4750,  'sms'=>'25,000',  'per'=>'K0.19/SMS', 'popular'=>true,  'save'=>'Save K3,750', 'label'=>'Gold'],
            ['price'=>'K9,000',  'raw'=>9000,  'sms'=>'50,000',  'per'=>'K0.18/SMS', 'popular'=>false, 'save'=>'Save K8,000', 'label'=>'Platinum'],
            ['price'=>'K17,000', 'raw'=>17000, 'sms'=>'100,000', 'per'=>'K0.17/SMS', 'popular'=>false, 'save'=>'Save K17,000','label'=>'Enterprise'],
        ];
        @endphp
        <div class="row justify-content-center">
          @foreach($smsBundles as $b)
          <div class="col-sm-6 col-lg-4" style="padding:10px;" data-aos="fade-up">
            <div style="border-radius:18px;border:{{ $b['popular'] ? '2px solid #F9A826' : '1px solid #e2e8f0' }};background:{{ $b['popular'] ? 'linear-gradient(135deg,#fffbeb,#fef3c7)' : '#fff' }};padding:30px 24px;height:100%;position:relative;box-shadow:{{ $b['popular'] ? '0 12px 40px rgba(249,168,38,0.25)' : '0 2px 16px rgba(0,0,0,0.06)' }};transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
              @if($b['popular'])
              <div style="position:absolute;top:-14px;left:50%;transform:translateX(-50%);background:#F9A826;color:#0a0f1e;padding:5px 20px;border-radius:20px;font-size:12px;font-weight:800;white-space:nowrap;">&#11088; Most Popular</div>
              @endif
              <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#94a3b8;margin-bottom:8px;">{{ $b['label'] }}</div>
              <div style="font-size:38px;font-weight:900;color:#0a0f1e;line-height:1;">{{ $b['price'] }}</div>
              <div style="margin:8px 0 18px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <span style="font-size:13px;color:#64748b;">{{ $b['per'] }}</span>
                @if($b['save'])
                <span style="background:#dcfce7;color:#166534;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;">{{ $b['save'] }}</span>
                @endif
              </div>
              <hr style="border:none;border-top:1px solid #f1f5f9;margin:18px 0;">
              <ul style="list-style:none;padding:0;margin:0 0 22px;font-size:14px;color:#374151;">
                <li style="padding:5px 0;">&#128232; <strong>{{ $b['sms'] }} SMS</strong> credits</li>
                <li style="padding:5px 0;">&#128246; MTN, Airtel &amp; Zamtel</li>
                <li style="padding:5px 0;">&#8734;&#65039; Credits never expire</li>
                <li style="padding:5px 0;">&#9989; Delivery reports</li>
              </ul>
              <a href="/admin/register" style="display:block;text-align:center;background:{{ $b['popular'] ? '#F9A826' : '#0a0f1e' }};color:{{ $b['popular'] ? '#0a0f1e' : '#fff' }};font-weight:700;font-size:14px;padding:13px;border-radius:11px;text-decoration:none;">Get Started &#8594;</a>
            </div>
          </div>
          @endforeach
        </div>
        {{-- Corporate --}}
        <div style="margin-top:28px;background:#0a0f1e;border-radius:18px;padding:30px 36px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:20px;">
          <div>
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#F9A826;margin-bottom:8px;">Corporate Plan</div>
            <div style="font-size:24px;font-weight:800;color:#f1f5f9;">250,000+ SMS &mdash; Custom Quote</div>
            <div style="font-size:14px;color:#64748b;margin-top:6px;">Dedicated account manager &middot; SLA guarantee &middot; Negotiated per-SMS rates</div>
          </div>
          <a href="#footer" style="background:#F9A826;color:#0a0f1e;font-weight:800;font-size:14px;padding:14px 30px;border-radius:11px;text-decoration:none;white-space:nowrap;">Contact Sales &#8594;</a>
        </div>
        <p style="text-align:center;margin-top:20px;color:#94a3b8;font-size:13px;">
          All prices in Zambian Kwacha (ZMW) &nbsp;&middot;&nbsp; Secure payments via Lenco &nbsp;&middot;&nbsp; No hidden fees
        </p>
      </div>

      {{-- INTERNATIONAL TAB --}}
      <div id="intl-tab" style="display:none;">
        <div class="row justify-content-center">
          <div class="col-sm-10 col-lg-6" style="padding:10px;" data-aos="fade-up">
            <div style="border-radius:20px;border:2px solid #F9A826;background:linear-gradient(135deg,#fffbeb,#fef3c7);padding:40px 36px;text-align:center;box-shadow:0 12px 40px rgba(249,168,38,0.2);position:relative;">
              <div style="position:absolute;top:-14px;left:50%;transform:translateX(-50%);background:#F9A826;color:#0a0f1e;padding:5px 22px;border-radius:20px;font-size:12px;font-weight:800;white-space:nowrap;">&#11088; All Inclusive</div>
              <div style="font-size:52px;margin-bottom:16px;">&#127758;</div>
              <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#92400e;margin-bottom:12px;">International SMS</div>
              <div style="font-size:52px;font-weight:900;color:#0a0f1e;line-height:1;">$0.389</div>
              <div style="font-size:15px;color:#64748b;margin:8px 0 28px;">per SMS &mdash; flat rate, any country</div>
              <hr style="border:none;border-top:1px solid rgba(0,0,0,0.08);margin:24px 0;">
              <div class="row" style="text-align:left;gap:0;">
                <div class="col-sm-4" style="padding:10px;">
                  <div style="background:#fff;border-radius:12px;padding:18px;height:100%;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                    <div style="font-size:28px;margin-bottom:10px;">&#127758;</div>
                    <div style="font-weight:700;color:#0a0f1e;font-size:14px;margin-bottom:6px;">International Numbers</div>
                    <div style="font-size:13px;color:#64748b;">Send to any country worldwide with automatic number formatting.</div>
                  </div>
                </div>
                <div class="col-sm-4" style="padding:10px;">
                  <div style="background:#fff;border-radius:12px;padding:18px;height:100%;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                    <div style="font-size:28px;margin-bottom:10px;">&#128100;</div>
                    <div style="font-weight:700;color:#0a0f1e;font-size:14px;margin-bottom:6px;">Free Sender ID</div>
                    <div style="font-size:13px;color:#64748b;">Your approved Sender ID is included — no extra charge.</div>
                  </div>
                </div>
                <div class="col-sm-4" style="padding:10px;">
                  <div style="background:#fff;border-radius:12px;padding:18px;height:100%;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                    <div style="font-size:28px;margin-bottom:10px;">&#9889;</div>
                    <div style="font-weight:700;color:#0a0f1e;font-size:14px;margin-bottom:6px;">Flash SMS</div>
                    <div style="font-size:13px;color:#64748b;">Instantly displayed on the recipient's screen. No inbox storage.</div>
                  </div>
                </div>
              </div>
              <a href="/admin/register" style="display:inline-block;margin-top:28px;background:#0a0f1e;color:#F9A826;font-weight:800;font-size:15px;padding:14px 36px;border-radius:12px;text-decoration:none;">Get Started &#8594;</a>
            </div>
          </div>
        </div>
        <p style="text-align:center;margin-top:24px;color:#94a3b8;font-size:13px;">
          Flat rate applies per SMS sent &nbsp;&middot;&nbsp; Billed in USD &nbsp;&middot;&nbsp; No hidden fees
        </p>
      </div>

    </div>
  </section>

  <!-- ======= Premium Channels Section ======= -->
  <section style="padding:80px 0;background:linear-gradient(135deg,#0a0f1e,#0d1b3e);">
    <div class="container" data-aos="fade-up">
      <div style="text-align:center;margin-bottom:48px;">
        <span style="background:rgba(249,168,38,0.15);color:#F9A826;border:1px solid rgba(249,168,38,0.3);padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;">Premium Channels</span>
        <h2 style="font-size:clamp(24px,4vw,38px);font-weight:800;color:#f1f5f9;margin:16px 0 10px;">WhatsApp &amp; Email — Included</h2>
        <p style="color:#64748b;font-size:15px;max-width:480px;margin:0 auto;">Extend your reach beyond SMS with two additional channels built right into your dashboard.</p>
      </div>
      <div class="row justify-content-center">
        <div class="col-sm-10 col-lg-5" style="padding:12px;">
          <div style="border-radius:20px;border:2px solid #25D366;background:rgba(37,211,102,0.05);padding:36px 30px;box-shadow:0 8px 40px rgba(37,211,102,0.15);transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
              <div style="background:#25D366;border-radius:14px;width:52px;height:52px;display:flex;align-items:center;justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
              </div>
              <div>
                <div style="font-size:12px;color:#25D366;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;">WhatsApp Business</div>
                <div style="font-size:28px;font-weight:900;color:#f1f5f9;">K500<span style="font-size:14px;font-weight:400;color:#64748b;">/month</span></div>
              </div>
            </div>
            <div style="margin-bottom:18px;"><span style="background:rgba(37,211,102,0.15);color:#4ade80;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;">&#127881; 10 free sends on sign-up</span></div>
            <ul style="list-style:none;padding:0;font-size:14px;color:#94a3b8;line-height:1.8;">
              <li>&#10003; Meta WhatsApp Cloud API</li>
              <li>&#10003; Approved message templates</li>
              <li>&#10003; Bulk multi-recipient sends</li>
              <li>&#10003; Delivery status tracking</li>
            </ul>
            <div style="margin-top:18px;padding:12px 14px;border-radius:10px;background:rgba(251,191,36,0.08);border:1px solid rgba(251,191,36,0.25);font-size:12px;color:#94a3b8;">
              <div style="font-weight:700;color:#fbbf24;margin-bottom:5px;">How Billing Works</div>
              <p style="margin:0 0 4px;"><strong style="color:#f1f5f9;">K500/month</strong> goes to SwiftSMS — covers API access, system, contact management &amp; embedded signup.</p>
              <p style="margin:0 0 10px;">Message costs are billed <strong style="color:#f1f5f9;">directly by Meta</strong>. Rates for Zambian users:</p>
              <table style="width:100%;font-size:11px;border-collapse:collapse;">
                <tr style="border-bottom:1px solid rgba(251,191,36,0.25);"><th style="padding:2px 0;text-align:left;color:#94a3b8;">Category</th><th style="padding:2px 0;text-align:right;color:#94a3b8;">USD/msg</th></tr>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.06);"><td style="padding:3px 0;">Marketing</td><td style="padding:3px 0;text-align:right;font-weight:700;color:#f1f5f9;">$0.0225</td></tr>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.06);"><td style="padding:3px 0;">Utility</td><td style="padding:3px 0;text-align:right;font-weight:700;color:#f1f5f9;">$0.0040</td></tr>
                <tr><td style="padding:3px 0;">Authentication</td><td style="padding:3px 0;text-align:right;font-weight:700;color:#f1f5f9;">$0.0040</td></tr>
              </table>
            </div>
            <a href="/admin/register" style="display:block;text-align:center;margin-top:20px;background:#25D366;color:#fff;font-weight:700;font-size:14px;padding:13px;border-radius:11px;text-decoration:none;">Activate WhatsApp &#8594;</a>
          </div>
        </div>
        <div class="col-sm-10 col-lg-5" style="padding:12px;">
          <div style="border-radius:20px;border:2px solid #4285F4;background:rgba(66,133,244,0.05);padding:36px 30px;box-shadow:0 8px 40px rgba(66,133,244,0.15);transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
              <div style="background:#4285F4;border-radius:14px;width:52px;height:52px;display:flex;align-items:center;justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              </div>
              <div>
                <div style="font-size:12px;color:#4285F4;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;">Bulk Email</div>
                <div style="font-size:28px;font-weight:900;color:#f1f5f9;">K500<span style="font-size:14px;font-weight:400;color:#64748b;">/month</span></div>
              </div>
            </div>
            <div style="margin-bottom:18px;"><span style="background:rgba(66,133,244,0.15);color:#93c5fd;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;">&#127881; 10 free sends on sign-up</span></div>
            <ul style="list-style:none;padding:0;font-size:14px;color:#94a3b8;line-height:1.8;">
              <li>&#10003; Single &amp; bulk sends</li>
              <li>&#10003; Gmail, Zoho, Outlook SMTP</li>
              <li>&#10003; Send to all contacts at once</li>
              <li>&#10003; Full delivery logs</li>
            </ul>
            <a href="/admin/register" style="display:block;text-align:center;margin-top:24px;background:#4285F4;color:#fff;font-weight:700;font-size:14px;padding:13px;border-radius:11px;text-decoration:none;">Activate Bulk Email &#8594;</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ======= CTA Section ======= -->
  <section style="padding:80px 0;background:#F9A826;">
    <div class="container" data-aos="zoom-in" style="text-align:center;">
      <h2 style="font-size:clamp(28px,5vw,48px);font-weight:900;color:#0a0f1e;margin-bottom:16px;">Ready to Start Sending?</h2>
      <p style="font-size:16px;color:rgba(10,15,30,0.7);max-width:480px;margin:0 auto 32px;">Join businesses across Zambia already using SwiftSMS to reach their customers instantly.</p>
      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
        <a href="/admin/register" style="background:#0a0f1e;color:#F9A826;font-weight:800;font-size:15px;padding:15px 36px;border-radius:12px;text-decoration:none;">&#128640; Create Free Account</a>
        <a href="{{route('api_docs')}}" style="background:rgba(10,15,30,0.1);color:#0a0f1e;border:2px solid rgba(10,15,30,0.2);font-weight:700;font-size:15px;padding:15px 36px;border-radius:12px;text-decoration:none;">&#128196; View API Docs</a>
      </div>
    </div>
  </section>

  <!-- Video Demo -->
  <section style="padding:60px 0;background:#f8fafc;">
    <div class="container" data-aos="fade-up">
      <div style="text-align:center;margin-bottom:32px;">
        <h3 style="font-size:24px;font-weight:800;color:#0a0f1e;">See SwiftSMS in Action</h3>
        <p style="color:#64748b;">Watch how easy it is to send bulk messages from your dashboard.</p>
      </div>
      <div style="position:relative;padding-bottom:56.25%;height:0;border-radius:16px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.15);">
        <video style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;" controls>
          <source src="{{asset('landing-page/assets/videos/BULK-SMS.mp4')}}">
          Your browser does not support the video tag.
        </video>
      </div>
    </div>
  </section>

  </main><!-- End #main -->

<div style="padding: 20px;">
  <center><button style="border:none; background-color: #F9A826;" type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#exampleModal">Submit Feedback</button></center>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><b>Feedback</h5></b></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form">

          <header class="section-header">
            <p>We would love to hear your thoughts, suggestions, concerns or problems with anything so we can improve!</p>
          </header>
          <form action="{{route('feedback.store')}}" method="post" role="form" class="php-email-form">
           
            <div class="form-group">
              <input type="text" name="name" class="form-control" id="name" placeholder="Your Name" data-rule="minlen:4" required/>
              <div class="validate"></div>
            </div>
            <div class="form-group">
              <input type="email" class="form-control" name="email" id="email" placeholder="Your Email" data-rule="email" required/>
              <div class="validate"></div>
            </div>
            <div class="form-group">
              <input type="phone" class="form-control" name="phone" id="subject" placeholder="Phone" data-rule="minlen:4" required/>
              <div class="validate"></div>
            </div>
            <label for="inlineRadio1">Rating</label>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="rating" id="inlineRadio1" value="1" required>
              <label class="form-check-label" for="inlineRadio1">1</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="rating" id="inlineRadio2" value="2" required>
              <label class="form-check-label" for="inlineRadio2">2</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="rating" id="inlineRadio3" value="3" required>
              <label class="form-check-label" for="inlineRadio3">3</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="rating" id="inlineRadio4" value="4" required>
              <label class="form-check-label" for="inlineRadio3">4</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="rating" id="inlineRadio5" value="5" required>
              <label class="form-check-label" for="inlineRadio3">5</label>
            </div>
            <div class="form-group">
              <textarea class="form-control" name="feedback" rows="5" data-rule="required" required placeholder="Feedback...."></textarea>
              <div class="validate"></div>
            </div>
  
            <div class="mb-3">
             <div class="loading" style="display: none;">Loading</div>
              <div class="error-message" style="background-color:yellow"></div>
              <div class="sent-message" style="display: none;">Your feedback has been sent. Thank you!</div>
            </div>
  
            <div class="text-center"><button style="background-color: #F9A826;" class="btn btn-primary" type="submit" title="Send Feedback">Send Feedback</button></div>
            
            <br>
            <br>
          </form>
  
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- End Feedback  -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="section-bg">
    <div class="footer-top">
      <div class="container">

        <div class="row">

          <div class="col-lg-6">

            <div class="row">

              <div class="col-sm-6">

                <div class="footer-info">
                  <h3>Swift-SMS</h3>
                  <p>This is the Bulk SMS Platform Powered, owned and created by MACRO-IT.</p>
                </div>

                <div class="footer-newsletter">
                  <h4>Our Newsletter</h4>
                <p>Get Free Email Updates!<br>Join us for FREE to get instant email updates!</p>
                  <form action="{{route('news-letter.store')}}" method="post" role="form" class="php-email-form">

                    <input type="email" name="email" placeholder="Your Email" required><input type="submit" value="Subscribe">
                    <div class="mb-3">
                      <div class="loading">Loading</div>
                      <div class="error-message" style="background-color:lime"></div>
                      <div class="sent-message">Your message has been sent. Thank you!</div>
                    </div>
                  </form>
                </div>

              </div>
              <div class="col-sm-6">
                <div class="footer-links">
                  <h4>Useful Links</h4>
                  <ul>
                    <!-- <li><a href="{{route('terms_and_conditions')}}">Terms and Conditions</a></li>
                    <li><a href="{{route('privacy_and_policy')}}">Privacy and Policy</a></li>                     -->
                    <li><a href="{{route('api_docs')}}">API</a></li>
                    <!--<li><a href="">Helpdesk</a></li>-->
                  </ul>
                </div>

                <div class="footer-links">
                  <h4>Contact Us</h4>
                  <p>
                    MACRO-IT<br>
                    <br>
                    <!-- Sunshare Buildings, Katima Mulilo road,Olympia, Lusaka<br> -->
                   <!-- <strong>Phone:</strong> +260..........<br>-->
                    <strong>Email:</strong> swiftsms@macroit.org<br>
                  </p>
                </div>

                <div class="social-links">
                  <a href="" class="twitter"><i class="fa fa-twitter"></i></a>
                  <a href="https://mobile.facebook.com/profile.php?id=100064834116610&_rdc=1&_rdr" class="facebook"><i class="fa fa-facebook"></i></a>
                  <a href="https://www.linkedin.com/company/96069606/" class="linkedin"><i class="fa fa-linkedin"></i></a>
                  <a href="" class="instagram"><i class="fa fa-instagram"></i></a>
                </div>

              </div>

            </div>

          </div>

          <div class="col-lg-6">

            <div class="form">

              <h4>Connect. Meet. Move.</h4>
              <p>Please fill out the form below to get in touch. We will contact you shortly.</p>

              <form action="{{route('contact-us.store')}}" method="post" role="form" class="php-email-form">
               
                <div class="form-group">
                  <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Your Name" data-rule="minlen:4" data-msg="Please enter at least 4 chars" />
                  @error('name')
                  <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                  <div class="validate"></div>
                </div>
                <div class="form-group">
                  <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" placeholder="Your Email" data-rule="email" data-msg="Please enter a valid email" />
                  @error('email')
                  <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                  <div class="validate"></div>
                </div>
                <div class="form-group">
                  <input type="phone" class="form-control @error('email') is-invalid @enderror" name="phone" id="subject" placeholder="Phone" data-rule="minlen:4" data-msg="Please enter your contact number" />
                  @error('phone')
                  <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                  <div class="validate"></div>
                </div>
                <div class="form-group">
                  <textarea class="form-control @error('message') is-invalid @enderror" name="message" rows="5" data-rule="required" data-msg="Please write something for us" placeholder="Message"></textarea>
                  @error('message')
                  <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                  <div class="validate"></div>
                </div>

                <div class="mb-3">
                  <div class="loading">Loading</div>
                  <div class="error-message" style="background-color:yellow"></div>
                  <div class="sent-message">Your message has been sent. Thank you!</div>
                </div>

                <div class="text-center"><button type="submit" title="Send Message">Send Message</button></div>
              </form>

            </div>

          </div>

        </div>

      </div>
    </div>
    <div class="container">
      <div class="copyright">
      Powered, owned and created By<strong> <a href="https://macroit.org">MACRO-IT</a></strong> 
      </div>
    </div>
  </footer><!-- End  Footer -->

  <a href="#" class="back-to-top"><i class="fa fa-chevron-up"></i></a>


