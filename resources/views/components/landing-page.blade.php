
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
         <h2 style="font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;"><span>Welcome</span> to SWIFTSMS</h2> 
          <div>
          
<p>Reach, Connect, Impact: Unleash the Power of Bulk SMS!</p>
<br>
          
            <a href="/admin/register" class="btn-get-started scrollto">Get Started</a>
            <a href="#footer" class="btn-get-started scrollto">Contact Us</a>
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
 <section id="why-us" class="why-us">
  <div class="container-fluid" data-aos="fade-up">

    <header class="section-header">
      <h3>Why choose us?</h3>     
    </header>
<br>
    <div class="row">

      <div class="col-lg-6" data-aos="zoom-in">
        <div class="why-us-img">
          <img src="{{asset('landing-page/assets/img/why-us.jpg')}}" alt="" class="img-fluid">
        </div>
      </div>

      <div class="col-lg-6">
        <div class="why-us-content">
          <p>Choose us for your bulk SMS needs and unlock the power of seamless communication with our feature-rich platform. We provide a comprehensive solution for sending SMS messages to MTN, Airtel, and Zamtel users, and we go the extra mile by offering API support, making integration with your existing systems a breeze.</p>
          <p>Whether you need to communicate with a large audience, or personalize your messages, our platform has got you covered.</p><br>
          <div class="features clearfix" data-aos="fade-up">
            <i class="fa fa-signal" style="color: #f058dc;"></i>
            <h4>Multi-network support</h4>
            <p>Send Bulk SMS Messages Instantly to MTN, Airtel, and Zamtel Users with ease.</p>
          </div>

          <div class="features clearfix" data-aos="fade-up">
            <i class="fa fa-object-group" style="color: #ffb774;"></i>
            <h4>Easy-to-use interface</h4>
            <p>Our platform is designed to be intuitive and user-friendly.</p>
          </div>

          <div class="features clearfix" data-aos="fade-up">
            <i class="fa fa-code" style="color: #589af1;"></i>
            <h4>API support</h4>
            <p>Seamlessly integrate our platform with your existing systems using our robust API, enabling streamlined communication workflows and enhancing your overall efficiency.</p>
          </div>

        </div>

      </div>

    </div>

  </div>

</section>

    <!-- ======= Services Section ======= -->
    <section id="services" class="services section-bg">
      <div class="container" data-aos="fade-up">

        <header class="section-header">
          <h3>Services</h3>
          <p>Our services</p>
        </header>

        <div class="row">

          <div class="col-md-6 col-lg-6 wow bounceInUp" data-aos="zoom-in">
            <div class="box">
              <div class="icon" style="background:white;"><span class="iconify" data-icon="ion:ribbon" data-inline="false" data-width="50" data-height="50" style="color: #e98e06;"></span></i></div>
              <h4 class="title"><a href="">Wide Range of Services</a></h4>
              <p class="description">Our bulk SMS platform offers a comprehensive range of services to cater to your communication needs. Whether you need to send transactional alerts, promotional messages, event reminders, or important notifications, our platform can handle it all.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-6" data-aos="zoom-in">
            <div class="box">
              <div class="icon" style="background:white;"><span class="iconify" data-icon="ion:checkmark-done" data-inline="false" data-width="50" data-height="50" style="color: #f058dc;"></span></div>
              <h4 class="title"><a href="">Reliable and Scalable Infrastructure</a></h4>
              <p class="description">Our platform is built on a robust and scalable infrastructure, ensuring reliable message delivery even during peak periods. We leverage advanced technologies and partnerships with telecom operators to guarantee high delivery rates. </p>
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

    -- ====== Pricing Section ======= -->
    <section id="pricing" class="pricing section-bg wow fadeInUp">

      <div class="container" data-aos="fade-up">

        <header class="section-header">
          <h3>Pricing</h3>
          <p>Our pricing structure is designed to provide excellent value for your investment, ensuring that you can effectively reach your audience without breaking the bank.</p>
        </header>

        <div class="row flex-items-xs-middle flex-items-xs-center">

         
          <div class="col-xs-12 col-lg-3" data-aos="fade-up" >
            <div class="card">
              <div class="card-header">
                <h3><span class="currency">K</span>500<span class="period"></span></h3>
              </div>
              <div class="card-block">
                <h4 class="card-title">
                  0 - 1000 SMS's
                </h4>
               <!--
               <form style="background-color: white" ><script src="https://checkout.razorpay.com/v1/payment-button.js" data-payment_button_id="pl_FlfdPPBTmttPRs"> </script> </form>
               -->
                </div>
            </div>
          </div>

          
          <div class="col-xs-12 col-lg-3" data-aos="fade-up">
            <div class="card">
              <div class="card-header">
                <h3><span class="currency">K</span>800<span class="period"></span></h3>
              </div>
              <div class="card-block">
                <h4 class="card-title">
                  1,001 - 2000 SMS's
                </h4>
               <!--
               <form style="background-color: white" ><script src="https://checkout.razorpay.com/v1/payment-button.js" data-payment_button_id="pl_FlfdPPBTmttPRs"> </script> </form>
               -->
                </div>
            </div>
          </div>

          
          <div class="col-xs-12 col-lg-3" data-aos="fade-up">
            <div class="card">
              <div class="card-header">
                <h3><span class="currency">K</span>1,100<span class="period"></span></h3>
              </div>
              <div class="card-block">
                <h4 class="card-title">
                  2,001 - 3000 SMS's
                </h4>
<!--
               <form style="background-color: white" ><script src="https://checkout.razorpay.com/v1/payment-button.js" data-payment_button_id="pl_FlfdPPBTmttPRs"> </script> </form>
               -->
              </div>
            </div>
          </div>



          <div class="col-xs-12 col-lg-3" data-aos="fade-up">
            <div class="card">
              <div class="card-header">
                <h3><span class="currency">K</span>1,400<span class="period"></span></h3>
              </div>
              <div class="card-block">
                <h4 class="card-title">
                  3,001 - 4000 SMS's
                </h4>
<!--
               <form style="background-color: white" ><script src="https://checkout.razorpay.com/v1/payment-button.js" data-payment_button_id="pl_FlfdPPBTmttPRs"> </script> </form>
               -->
              </div>
            </div>
          </div>



<br>

          <div class="col-xs-12 col-lg-3" data-aos="fade-up">
            <div class="card">
              <div class="card-header">
                <h3><span class="currency">K</span>1,700<span class="period"></span></h3>
              </div>
              <div class="card-block">
                <h4 class="card-title">
                  4,001 - 5000 SMS's
                </h4>
               <!--
               <form style="background-color: white" ><script src="https://checkout.razorpay.com/v1/payment-button.js" data-payment_button_id="pl_FlfdPPBTmttPRs"> </script> </form>
               -->
                </div>
            </div>
          </div>

          
          <div class="col-xs-12 col-lg-3" data-aos="fade-up">
            <div class="card">
              <div class="card-header">
                <h3><span class="currency">K</span>2000<span class="period"></span></h3>
              </div>
              <div class="card-block">
                <h4 class="card-title">
                  5,001 - 6000 SMS's
                </h4>
               <!--
               <form style="background-color: white" ><script src="https://checkout.razorpay.com/v1/payment-button.js" data-payment_button_id="pl_FlfdPPBTmttPRs"> </script> </form>
               -->
                </div>
            </div>
          </div>

          
          <div class="col-xs-12 col-lg-3" data-aos="fade-up">
            <div class="card">
              <div class="card-header">
                <h3><span class="currency">K</span>2,200<span class="period"></span></h3>
              </div>
              <div class="card-block">
                <h4 class="card-title">
                  6,001 - 7000 SMS's
                </h4>
               <!--
               <form style="background-color: white" ><script src="https://checkout.razorpay.com/v1/payment-button.js" data-payment_button_id="pl_FlfdPPBTmttPRs"> </script> </form>
               -->
              </div>
            </div>
          </div>



          <div class="col-xs-12 col-lg-3" data-aos="fade-up">
            <div class="card">
              <div class="card-header">
                <h3><span class="currency">K</span>2,500<span class="period"></span></h3>
              </div>
              <div class="card-block">
                <h4 class="card-title">
                  7,001 - 8000 SMS's
                </h4>
                <!--
               <form style="background-color: white" ><script src="https://checkout.razorpay.com/v1/payment-button.js" data-payment_button_id="pl_FlfdPPBTmttPRs"> </script> </form>
               -->
              </div>
            </div>
          </div>












        </div>
      </div>

    </section><!-- End Pricing Section -->

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
                    <strong>Email:</strong> info@swift-sms.net<br>
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
      Powered, owned and created By<strong> <a href="">MACRO-IT</a></strong> 
      </div>
    </div>
  </footer><!-- End  Footer -->

  <a href="#" class="back-to-top"><i class="fa fa-chevron-up"></i></a>


