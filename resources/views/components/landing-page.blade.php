
  <!-- ======= Top Bar ======= -->
  <div id="topbar" class="d-none d-lg-flex align-items-end fixed-top topbar-transparent">
    <div class="container d-flex justify-content-end">
      <div class="social-links">
        <a href="" class="twitter"><i class="fa fa-twitter"></i></a>
        <a href="https://mobile.facebook.com/profile.php?id=100064834116610&_rdc=1&_rdr" class="facebook"><i class="fa fa-facebook"></i></a>
        <a href="https://www.linkedin.com/company/96069606/" class="linkedin"><i class="fa fa-linkedin"></i></a>
        <a href="" class="instagram"><i class="fa fa-instagram"></i></a>
      </div>
    </div>
  </div>

  <!-- ======= Header ======= -->
  <header id="header" class="fixed-top header-transparent">
    <div class="container d-flex align-items-center">

     <!-- <h1 class="logo mr-auto"><a href="index.html">CGV.IN.NET</a></h1> -->
      <!-- Uncomment below if you prefer to use an image logo -->
      <a href="{{'/'}}" class="logo mr-auto"><img src="{{asset('landing-page/img/logo/logo.jpg')}}" style="border-radius: 100%;" alt="" class="img-fluid"></a>

      <nav class="main-nav d-none d-lg-block dark">
        <ul>
          <li class="active"><a href="{{'/'}}">HOME</a></li>
          <li><a href="#why-us">ABOUT US</a></li>
          <li><a href="#services">SERVICES</a></li>
          <li><a href="#pricing">PRICING</a></li>
          <li><a href="{{route('api_docs')}}">API</a></li>
          <li><a href="#footer">CONTACT US</a></li>
          <li><a href="/admin/register">GET STARTED</a></li>
          <li ><a href="/admin/login"><i class="fa fa-sign-in" aria-hidden="true"></i> LOGIN</a></li>
        </ul>
      </nav><!-- .main-nav-->

    </div>
  </header><!-- End Header -->

  <!-- ======= Hero Section ======= -->
  <section id="hero" class="clearfix">
    <div class="container d-flex h-100">
      <div class="row justify-content-center align-self-center">
        <div class="col-md-6 intro-info order-md-first order-last">
          <h2 style="font-family:'Trebuchet MS',Arial,sans-serif;line-height:1.3;">
            <span>Reach Anyone,</span><br>Anywhere in the World
          </h2>
          <div>
            <p style="font-size:17px;opacity:0.9;">Bulk SMS · WhatsApp Business · Bulk Email — all in one powerful platform built for Zambian businesses going global.</p>
            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:20px;">
              <a href="/admin/register" class="btn-get-started scrollto">🚀 Get Started Free</a>
              <a href="#pricing" class="btn-get-started scrollto" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.4);">See Pricing</a>
            </div>
            <p style="margin-top:16px;font-size:13px;opacity:0.75;">✅ No setup fees &nbsp;·&nbsp; ✅ 10 free sends on Email &amp; WhatsApp &nbsp;·&nbsp; ✅ Pay as you go</p>
          </div>
        </div>
        <div class="col-md-6 intro-img order-md-last order-first">
          <img src="{{asset('landing-page/assets/img/illustration.svg')}}" alt="" class="img-fluid">
        </div>
      </div>
    </div>
  </section><!-- End Hero -->

  <main id="main">
 <!-- ======= Why Us Section ======= -->
 <section id="why-us" class="why-us" style="padding:60px 0;background:#fff;">
  <div class="container" data-aos="fade-up">

    <header class="section-header" style="text-align:center;margin-bottom:40px;">
      <h3 style="font-size:28px;font-weight:800;color:#111827;">Why SwiftSMS?</h3>
      <p style="color:#6b7280;font-size:15px;max-width:520px;margin:10px auto 0;">Built for Zambia, designed for growth — the only platform that combines SMS, WhatsApp, and Email in one place.</p>
    </header>

    <div class="row align-items-center">

      <div class="col-lg-6" data-aos="zoom-in">
        <img src="{{asset('landing-page/assets/img/why-us.jpg')}}" alt="" class="img-fluid" style="border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,0.12);">
      </div>

      <div class="col-lg-6" style="padding:30px 20px;">
        <p style="font-size:15px;color:#6b7280;line-height:1.8;margin-bottom:24px;">From a small startup to a national enterprise — SwiftSMS gives you the tools to reach your audience instantly via SMS, WhatsApp, or Email with zero technical overhead.</p>

        <div style="display:flex;gap:14px;margin-bottom:20px;">
          <div style="flex-shrink:0;width:42px;height:42px;background:#fff0f9;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">📡</div>
          <div>
            <h4 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:4px;">Multi-network &amp; international</h4>
            <p style="font-size:13px;color:#6b7280;margin:0;">Send to MTN, Airtel, Zamtel and international numbers worldwide in one click.</p>
          </div>
        </div>

        <div style="display:flex;gap:14px;margin-bottom:20px;">
          <div style="flex-shrink:0;width:42px;height:42px;background:#fffbeb;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">⚡</div>
          <div>
            <h4 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:4px;">Intuitive &amp; fast</h4>
            <p style="font-size:13px;color:#6b7280;margin:0;">Go from sign-up to sending in minutes. No training needed — built for everyone.</p>
          </div>
        </div>

        <div style="display:flex;gap:14px;margin-bottom:20px;">
          <div style="flex-shrink:0;width:42px;height:42px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">🔌</div>
          <div>
            <h4 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:4px;">Developer-friendly API</h4>
            <p style="font-size:13px;color:#6b7280;margin:0;">REST API for seamless integration into your app, CRM, or website. Flash &amp; scheduled SMS included.</p>
          </div>
        </div>

        <a href="/admin/register" style="display:inline-block;margin-top:8px;background:#F9A826;color:#fff;font-weight:700;font-size:14px;padding:12px 28px;border-radius:10px;text-decoration:none;">Start for Free &#8594;</a>
      </div>

    </div>

  </div>

</section>

    <!-- ======= Services Section ======= -->
    <section id="services" class="services section-bg" style="padding:60px 0;">
      <div class="container" data-aos="fade-up">

        <header class="section-header" style="text-align:center;margin-bottom:40px;">
          <h3 style="font-size:28px;font-weight:800;color:#111827;">Everything You Need to Communicate</h3>
          <p style="color:#6b7280;font-size:15px;max-width:580px;margin:10px auto 0;">One platform. Three powerful channels. Reach your audience wherever they are.</p>
        </header>

        <div class="row">

          <div class="col-md-6 col-lg-3 wow bounceInUp" data-aos="zoom-in" style="padding:10px;">
            <div class="box" style="border-radius:14px;border:1px solid #e5e7eb;padding:28px;height:100%;box-shadow:0 2px 12px rgba(0,0,0,0.06);transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
              <div class="icon" style="background:#fff8ec;width:60px;height:60px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                <span class="iconify" data-icon="ion:chatbubbles" data-inline="false" data-width="34" data-height="34" style="color:#e98e06;"></span>
              </div>
              <h4 class="title" style="font-size:16px;font-weight:700;color:#111827;margin-bottom:10px;"><a href="" style="color:inherit;text-decoration:none;">Bulk SMS</a></h4>
              <p class="description" style="font-size:13px;color:#6b7280;line-height:1.6;">Send transactional alerts, promotions, and reminders to MTN, Airtel &amp; Zamtel. Now with international number support and flash/scheduled SMS.</p>
            </div>
          </div>

          <div class="col-md-6 col-lg-3" data-aos="zoom-in" style="padding:10px;">
            <div class="box" style="border-radius:14px;border:1px solid #e5e7eb;padding:28px;height:100%;box-shadow:0 2px 12px rgba(0,0,0,0.06);transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
              <div class="icon" style="background:#f0fdf4;width:60px;height:60px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                <span class="iconify" data-icon="ion:checkmark-done" data-inline="false" data-width="34" data-height="34" style="color:#22c55e;"></span>
              </div>
              <h4 class="title" style="font-size:16px;font-weight:700;color:#111827;margin-bottom:10px;"><a href="" style="color:inherit;text-decoration:none;">Reliable Infrastructure</a></h4>
              <p class="description" style="font-size:13px;color:#6b7280;line-height:1.6;">Built on a robust, scalable platform. Advanced delivery tracking, contact management, CSV imports, and a full API for your developers.</p>
            </div>
          </div>

          <div class="col-md-6 col-lg-3 mt-4 mt-lg-0" data-aos="zoom-in" style="padding:10px;">
            <div class="box" style="border-radius:14px;border:2px solid #25D366;padding:28px;height:100%;box-shadow:0 2px 20px rgba(37,211,102,0.15);transition:transform 0.2s;position:relative;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
              <span style="position:absolute;top:12px;right:14px;background:#fff3cd;color:#856404;padding:2px 9px;border-radius:20px;font-size:10px;font-weight:700;">NEW</span>
              <div class="icon" style="background:#f0fdf4;width:60px;height:60px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="#25D366">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
              </div>
              <h4 class="title" style="font-size:16px;font-weight:700;color:#111827;margin-bottom:10px;"><a href="" style="color:inherit;text-decoration:none;">WhatsApp Business</a></h4>
              <p class="description" style="font-size:13px;color:#6b7280;line-height:1.6;">Send approved template messages via Meta's WhatsApp Cloud API. Bulk multi-recipient sends with delivery tracking. <strong>K500/month</strong> + 10 free sends.</p>
            </div>
          </div>

          <div class="col-md-6 col-lg-3 mt-4 mt-lg-0" data-aos="zoom-in" style="padding:10px;">
            <div class="box" style="border-radius:14px;border:2px solid #4285F4;padding:28px;height:100%;box-shadow:0 2px 20px rgba(66,133,244,0.15);transition:transform 0.2s;position:relative;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
              <span style="position:absolute;top:12px;right:14px;background:#fff3cd;color:#856404;padding:2px 9px;border-radius:20px;font-size:10px;font-weight:700;">NEW</span>
              <div class="icon" style="background:#eff6ff;width:60px;height:60px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" fill="none" stroke="#4285F4" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
              </div>
              <h4 class="title" style="font-size:16px;font-weight:700;color:#111827;margin-bottom:10px;"><a href="" style="color:inherit;text-decoration:none;">Bulk Email</a></h4>
              <p class="description" style="font-size:13px;color:#6b7280;line-height:1.6;">Send rich HTML emails via Gmail, Zoho, Outlook &amp; more using your own SMTP. Single or bulk sends to all contacts. Full delivery logs. <strong>K300/month</strong> + 10 free sends.</p>
            </div>
          </div>

        </div>
      </div>
    </section><!-- End Services Section -->

   

    <!-- ======= Call To Action Section ======= 
    <section id="call-to-action" class="call-to-action">
      <div class="container" data-aos="zoom-out">
        <div class="row">
          <div class="col-lg-9 text-center text-lg-left">
            <h3 class="cta-title">Call To Action</h3>
            <p class="cta-text">We are 24x7 available at your comfort-zone. First time you can contact us via contact form. Customers can contact us via customer dashboard.</p>
          </div>
          <div class="col-lg-3 cta-btn-container text-center">
            <a class="cta-btn align-middle" href="#footer">Call To Action</a>
          </div>
        </div>

      </div>
    </section>  End Call To Action Section -->

    <!-- ======= Features Section ======= -->
    <!-- <section id="features" class="features">
      <div class="container" data-aos="fade-up">

        <div class="row feature-item">
          <div class="col-lg-6" data-aos="fade-right">
            <img src="assets/img/features-1.svg" class="img-fluid" alt="">
          </div>
          <div class="col-lg-6 wow fadeInUp pt-5 pt-lg-0" data-aos="fade-left" >
            <h4>Voluptatem dignissimos provident quasi corporis voluptates sit assumenda.</h4>
            <p>
              Ipsum in aspernatur ut possimus sint. Quia omnis est occaecati possimus ea. Quas molestiae perspiciatis occaecati qui rerum. Deleniti quod porro sed quisquam saepe. Numquam mollitia recusandae non ad at et a.
            </p>
            <p>
              Ad vitae recusandae odit possimus. Quaerat cum ipsum corrupti. Odit qui asperiores ea corporis deserunt veritatis quidem expedita perferendis. Qui rerum eligendi ex doloribus quia sit. Porro rerum eum eum.
            </p>
          </div>
        </div>

        <div class="row feature-item mt-5 pt-5">
          <div class="col-lg-6 wow fadeInUp order-1 order-lg-2" data-aos="fade-left" >
            <img src="assets/img/features-2.svg" class="img-fluid" alt="">
          </div>

          <div class="col-lg-6 wow fadeInUp pt-4 pt-lg-0 order-2 order-lg-1" data-aos="fade-right" >
            <h4>Neque saepe temporibus repellat ea ipsum et. Id vel et quia tempora facere reprehenderit.</h4>
            <p>
              Delectus alias ut incidunt delectus nam placeat in consequatur. Sed cupiditate quia ea quis. Voluptas nemo qui aut distinctio. Cumque fugit earum est quam officiis numquam. Ducimus corporis autem at blanditiis beatae incidunt sunt.
            </p>
            <p>
              Voluptas saepe natus quidem blanditiis. Non sunt impedit voluptas mollitia beatae. Qui esse molestias. Laudantium libero nisi vitae debitis. Dolorem cupiditate est perferendis iusto.
            </p>
            <p>
              Eum quia in. Magni quas ipsum a. Quis ex voluptatem inventore sint quia modi. Numquam est aut fuga mollitia exercitationem nam accusantium provident quia.
            </p>
          </div>

        </div>

      </div>
    </section> -->
   <!-- End Features Section 



    - ======= Testimonials Section ======= 
    <section id="testimonials" class="testimonials">
      <div class="container" data-aos="zoom-in">

        <header class="section-header">
          <h3>Testimonials</h3>
        </header>

        <div class="row justify-content-center">
          <div class="col-lg-8">

            <div class="owl-carousel testimonials-carousel">

              <div class="testimonial-item">
                <img src="assets/img/testimonial-1.jpg" class="testimonial-img" alt="">
                <h3>Saul Goodman</h3>
                <h4>C.E.O &amp; Founder</h4>
                <p>
                  Proin iaculis purus consequat sem cure digni ssim donec porttitora entum suscipit rhoncus. Accusantium quam, ultricies eget id, aliquam eget nibh et. Maecen aliquam, risus at semper.
                </p>
              </div>

              <div class="testimonial-item">
                <img src="assets/img/testimonial-2.jpg" class="testimonial-img" alt="">
                <h3>Sara Wilsson</h3>
                <h4>Designer</h4>
                <p>
                  Export tempor illum tamen malis malis eram quae irure esse labore quem cillum quid cillum eram malis quorum velit fore eram velit sunt aliqua noster fugiat irure amet legam anim culpa.
                </p>
              </div>

              <div class="testimonial-item">
                <img src="assets/img/testimonial-3.jpg" class="testimonial-img" alt="">
                <h3>Jena Karlis</h3>
                <h4>Store Owner</h4>
                <p>
                  Enim nisi quem export duis labore cillum quae magna enim sint quorum nulla quem veniam duis minim tempor labore quem eram duis noster aute amet eram fore quis sint minim.
                </p>
              </div>

              <div class="testimonial-item">
                <img src="assets/img/testimonial-4.jpg" class="testimonial-img" alt="">
                <h3>Matt Brandon</h3>
                <h4>Freelancer</h4>
                <p>
                  Fugiat enim eram quae cillum dolore dolor amet nulla culpa multos export minim fugiat minim velit minim dolor enim duis veniam ipsum anim magna sunt elit fore quem dolore labore illum veniam.
                </p>
              </div>

            </div>

          </div>
        </div>

      </div>
   

   - ======= Team Section ======= --
    <section id="team" class="team section-bg">
      <div class="container" data-aos="fade-up">
        <div class="section-header">
          <h3>Team</h3>
          <p>Our team include experienced tech experts</p>
        </div>

        <div class="row">


          <div class="offset-lg-3 col-lg-3 col-md-6" data-aos="fade-up" >
            <div class="member">
              <img src="img/Vignesh.jpg" class="img-fluid" alt="Vignesh Shetty">
              <div class="member-info">
                <div class="member-info-content">
                  <h4>Vignesh Shetty</h4>
                  <span>Developer</span>
                  <div class="social">
                    <a href="https://linkedin/vigneshshettyin"><i class="fa fa-linkedin"></i></a>
                    <a href="https://github.com/vigneshshettyin"><i class="fa fa-github"></i></a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6" data-aos="fade-up" >
            <div class="member">
              <img src="img/Vaibhav.jpg" class="img-fluid" alt="">
              <div class="member-info">
                <div class="member-info-content">
                  <h4>Vaibhav Suvarna</h4>
                  <span>Designer</span>
                  <div class="social">
                  <a href="https://linkedin.com/in/vaibhav-k-suvarna "><i class="fa fa-linkedin"></i></a>
                  <a href="https://github.com/Dreamer007VS"><i class="fa fa-github"></i></a>
                  </div>
                </div>
              </div>
            </div>
          </div>



        </div>

      </div>
    </section>- End Team Section --

-- ======= Clients Section ======= --
    <section id="clients" class="clients">
      <div class="container" data-aos="zoom-in">

        <header class="section-header">
          <h3>Technologies Used</h3>
        </header>

        <div class="owl-carousel clients-carousel">
          <img src="img/1.png" alt="">
          <img src="img/2.png" alt="">
          <img src="img/3.png" alt="">
          <img src="img/4.png" alt="">
          <img src="img/5.png" alt="">
          <img src="img/6.png" alt="">
        </div>

      </div>
    </section>

    <!-- ======= SMS Pricing Section ======= -->
    <section id="pricing" class="section-bg wow fadeInUp" style="padding:60px 0;">
      <div class="container" data-aos="fade-up">

        <header class="section-header" style="text-align:center;margin-bottom:40px;">
          <h3 style="font-size:28px;font-weight:800;color:#111827;">Bulk SMS Pricing</h3>
          <p style="color:#6b7280;font-size:15px;max-width:560px;margin:10px auto 0;">
            Pay only for what you need. Bigger bundles = lower cost per SMS. Credits never expire.
          </p>
        </header>

        @php
        $smsBundles = [
            ['price'=>'K800',   'sms'=>'1,000',  'per'=>'K0.80/SMS', 'popular'=>false, 'save'=>null,         'label'=>'Starter'],
            ['price'=>'K1,500', 'sms'=>'2,000',  'per'=>'K0.75/SMS', 'popular'=>false, 'save'=>'Save K100',  'label'=>'Standard'],
            ['price'=>'K2,100', 'sms'=>'3,000',  'per'=>'K0.70/SMS', 'popular'=>false, 'save'=>'Save K300',  'label'=>'Business'],
            ['price'=>'K3,500', 'sms'=>'5,000',  'per'=>'K0.70/SMS', 'popular'=>true,  'save'=>'Save K500',  'label'=>'Growth'],
            ['price'=>'K5,200', 'sms'=>'8,000',  'per'=>'K0.65/SMS', 'popular'=>false, 'save'=>'Save K1,200','label'=>'Pro'],
            ['price'=>'K6,000', 'sms'=>'10,000', 'per'=>'K0.60/SMS', 'popular'=>false, 'save'=>'Save K2,000','label'=>'Enterprise'],
        ];
        @endphp

        <div class="row justify-content-center">
          @foreach($smsBundles as $b)
          <div class="col-xs-12 col-sm-6 col-lg-4" data-aos="fade-up" style="padding:10px;">
            <div style="
                border-radius:16px;
                border:{{ $b['popular'] ? '2px solid #f59e0b' : '1px solid #e5e7eb' }};
                background:{{ $b['popular'] ? 'linear-gradient(135deg,#fffbeb,#fef3c7)' : '#fff' }};
                padding:28px 24px;height:100%;position:relative;
                box-shadow:{{ $b['popular'] ? '0 8px 30px rgba(245,158,11,0.2)' : '0 2px 12px rgba(0,0,0,0.06)' }};
                transition:transform 0.2s;" 
                onmouseover="this.style.transform='translateY(-4px)'" 
                onmouseout="this.style.transform='translateY(0)'">

              @if($b['popular'])
              <div style="position:absolute;top:-13px;left:50%;transform:translateX(-50%);background:#f59e0b;color:#fff;padding:4px 18px;border-radius:20px;font-size:12px;font-weight:700;white-space:nowrap;">&#11088; Most Popular</div>
              @endif

              <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#9ca3af;margin-bottom:8px;">{{ $b['label'] }}</div>
              <div style="font-size:36px;font-weight:800;color:#111827;line-height:1;">{{ $b['price'] }}</div>
              <div style="margin:8px 0 16px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <span style="font-size:13px;color:#6b7280;">{{ $b['per'] }}</span>
                @if($b['save'])
                <span style="background:#dcfce7;color:#166534;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;">{{ $b['save'] }}</span>
                @endif
              </div>
              <hr style="border:none;border-top:1px solid #f3f4f6;margin:16px 0;">
              <ul style="list-style:none;padding:0;margin:0 0 20px;font-size:14px;color:#374151;">
                <li style="padding:5px 0;">&#128232; <strong>{{ $b['sms'] }} SMS</strong> credits</li>
                <li style="padding:5px 0;">&#127758; Local &amp; international numbers</li>
                <li style="padding:5px 0;">&#128246; MTN, Airtel &amp; Zamtel</li>
                <li style="padding:5px 0;">&#8734;&#65039; Credits never expire</li>
              </ul>
              <a href="/admin/register" style="display:block;text-align:center;
                background:{{ $b['popular'] ? '#f59e0b' : '#F9A826' }};
                color:#fff;font-weight:700;font-size:14px;padding:12px;
                border-radius:10px;text-decoration:none;transition:opacity 0.2s;"
                onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                Get Started &#8594;
              </a>
            </div>
          </div>
          @endforeach
        </div>

        <p style="text-align:center;margin-top:30px;color:#9ca3af;font-size:13px;">
          All prices in Zambian Kwacha (ZMW) &nbsp;&middot;&nbsp; Secure payments via Lenco &nbsp;&middot;&nbsp; No hidden fees
        </p>
      </div>
    </section><!-- End SMS Pricing Section -->

    <!-- ======= Premium Messaging Pricing Section ======= -->
    <section class="section-bg wow fadeInUp" style="padding:60px 0;background:linear-gradient(135deg,#0f172a,#1e293b);">
      <div class="container" data-aos="fade-up">

        <header class="section-header" style="text-align:center;margin-bottom:40px;">
          <h3 style="font-size:28px;font-weight:800;color:#f1f5f9;">Premium Messaging Channels</h3>
          <p style="color:#94a3b8;font-size:15px;max-width:520px;margin:10px auto 0;">Extend your reach with WhatsApp &amp; Email — both include 10 free sends when you sign up.</p>
        </header>

        <div class="row justify-content-center">

          {{-- WhatsApp --}}
          <div class="col-xs-12 col-sm-6 col-lg-4" data-aos="fade-up" style="padding:10px;">
            <div style="border-radius:18px;border:2px solid #25D366;background:#0f2018;padding:32px 28px;height:100%;box-shadow:0 8px 32px rgba(37,211,102,0.2);transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;">
                <div style="background:#25D366;border-radius:10px;width:42px;height:42px;display:flex;align-items:center;justify-content:center;">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="white">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                  </svg>
                </div>
                <div>
                  <div style="font-size:11px;color:#25D366;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;">WhatsApp Business</div>
                  <div style="font-size:26px;font-weight:800;color:#f1f5f9;line-height:1.1;">K500<span style="font-size:14px;font-weight:400;color:#94a3b8;">/month</span></div>
                </div>
              </div>
              <div style="margin-bottom:18px;">
                <span style="background:rgba(37,211,102,0.15);color:#4ade80;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;">&#127881; 10 free sends on sign-up</span>
              </div>
              <ul style="list-style:none;padding:0;margin:0;font-size:13px;color:#94a3b8;line-height:1.7;">
                <li>&#10003; Meta WhatsApp Cloud API</li>
                <li>&#10003; Approved message templates</li>
                <li>&#10003; Bulk multi-recipient sends</li>
                <li>&#10003; Delivery status tracking</li>
                <li>&#10003; Full message history</li>
              </ul>
              <a href="/admin/register" style="display:block;text-align:center;margin-top:22px;background:#25D366;color:#fff;font-weight:700;font-size:14px;padding:12px;border-radius:10px;text-decoration:none;">Activate WhatsApp &#8594;</a>
            </div>
          </div>

          {{-- Email --}}
          <div class="col-xs-12 col-sm-6 col-lg-4" data-aos="fade-up" style="padding:10px;">
            <div style="border-radius:18px;border:2px solid #4285F4;background:#0d1a2e;padding:32px 28px;height:100%;box-shadow:0 8px 32px rgba(66,133,244,0.2);transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;">
                <div style="background:#4285F4;border-radius:10px;width:42px;height:42px;display:flex;align-items:center;justify-content:center;">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div>
                  <div style="font-size:11px;color:#4285F4;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;">Bulk Email</div>
                  <div style="font-size:26px;font-weight:800;color:#f1f5f9;line-height:1.1;">K300<span style="font-size:14px;font-weight:400;color:#94a3b8;">/month</span></div>
                </div>
              </div>
              <div style="margin-bottom:18px;">
                <span style="background:rgba(66,133,244,0.15);color:#60a5fa;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;">&#127881; 10 free sends on sign-up</span>
              </div>
              <ul style="list-style:none;padding:0;margin:0;font-size:13px;color:#94a3b8;line-height:1.7;">
                <li>&#10003; Single &amp; bulk email sends</li>
                <li>&#10003; Your own SMTP server</li>
                <li>&#10003; Gmail, Zoho, Outlook &amp; more</li>
                <li>&#10003; Send to all contacts at once</li>
                <li>&#10003; Full delivery logs</li>
              </ul>
              <a href="/admin/register" style="display:block;text-align:center;margin-top:22px;background:#4285F4;color:#fff;font-weight:700;font-size:14px;padding:12px;border-radius:10px;text-decoration:none;">Activate Email &#8594;</a>
            </div>
          </div>

        </div>
      </div>
    </section><!-- End Premium Messaging Pricing Section -->

    <section  class="pricing section-bg wow fadeInUp">
      <div class="container" data-aos="fade-up">
        <header class="section-header">
          <h3>Video Demo</h3>
          <p>Discover the Power of Bulk SMS Messaging: Connect, Engage, and Grow with SwiftSMS. Watch the Video to Learn More!</p>
        </header>
        <div style="position: relative; padding-bottom: 56.25%; height: 0;">

        <video class="embed-responsive-item"  controls>
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
                    <li><a href="{{route('terms_and_conditions')}}">Terms and Conditions</a></li>
                    <li><a href="{{route('privacy_and_policy')}}">Privacy and Policy</a></li>                    
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


