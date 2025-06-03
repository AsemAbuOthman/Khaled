<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>من نحن - منصة إعارة الأدوات الطبية</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">

 
</head>
<body>
    <?php include("./partials/header-1.php"); ?>

  <section class="about-hero">
    <h1>منصة إعارة الأدوات الطبية</h1>
    <p>منصة مجتمعية تهدف لتقديم حلول عملية لاستخدام الأدوات الطبية وتقليل التكاليف على المرضى</p>
  </section>

  <div class="about-container">
    <div class="about-section">
      <h2>من نحن</h2>
      <p>نحن منصة مجتمعية رائدة تهدف إلى تسهيل إعارة الأدوات الطبية لمن هم بحاجة إليها. انطلقت رؤيتنا من القيم الإسلامية الأصيلة التي تحث على التعاون، والتكافل، والتراحم بين أفراد المجتمع.</p>
      
      <p>نؤمن بأن إعانة المرضى وتخفيف آلامهم لا تقتصر على العلاج الطبي فقط، بل تشمل تقديم الوسائل التي تساعدهم على التعافي والاستمرار في حياتهم الطبيعية. هذا هو الهدف الأساسي الذي تسعى إليه منصتنا من خلال ربط من يملك أدوات طبية غير مستخدمة، بمن يحتاج إليها في الوقت المناسب.</p>
      
      <div class="quote">
        <p>من نفّس عن مؤمن كربةً من كرب الدنيا، نفّس الله عنه كربةً من كرب يوم القيامة</p>
        <p>رسول الله ﷺ</p>
      </div>
      
      <p>تأسست منصتنا في عام 2023 كمبادرة مجتمعية تطوعية، وتم تطويرها لتصبح المنصة الأولى من نوعها في المنطقة. نحن فريق من المتخصصين في المجال الطبي والتقني، نسعى لتقديم حلول مبتكرة تساهم في بناء مجتمع صحي متكافل.</p>
    </div>

    <div class="about-section">
      <h2>رؤيتنا ورسالتنا</h2>
      <p><strong>الرؤية:</strong> أن نكون المنصة الرائدة في تمكين التكافل الصحي بين أفراد المجتمع من خلال تسهيل إعارة الأدوات الطبية.</p>
      
      <p><strong>الرسالة:</strong> تقديم حلول مبتكرة تربط بين من يحتاجون للأدوات الطبية ومن يمتلكونها، لتحقيق التكافل المجتمعي وتخفيف الأعباء المادية عن المرضى.</p>
      
      <div class="values-grid">
        <div class="value-card">
          <i class="fas fa-hands-helping"></i>
          <h3>التكافل</h3>
          <p>نسعى لتعزيز روح التكافل والتعاون بين أفراد المجتمع لمساعدة المرضى والمحتاجين.</p>
        </div>
        
        <div class="value-card">
          <i class="fas fa-heartbeat"></i>
          <h3>التخفيف</h3>
          <p>نهدف لتخفيف الأعباء المادية والنفسية عن المرضى وعائلاتهم خلال رحلة العلاج.</p>
        </div>
        
        <div class="value-card">
          <i class="fas fa-shield-alt"></i>
          <h3>الجودة</h3>
          <p>نلتزم بأعلى معايير الجودة والسلامة في جميع الأدوات المتاحة على المنصة.</p>
        </div>
        
        <div class="value-card">
          <i class="fas fa-lightbulb"></i>
          <h3>الابتكار</h3>
          <p>نطور حلولاً تقنية مبتكرة لتحسين تجربة المستخدمين وضمان سهولة الاستخدام.</p>
        </div>
      </div>
    </div>

    <div class="cta-section">
      <h2>انضم إلى مجتمعنا المتكافل</h2>
      <p>سواء كنت تمتلك أدوات طبية ترغب في إعارتها، أو كنت بحاجة لأداة معينة، سجل معنا الآن وكن جزءًا من مجتمعنا الصحي المتكافل</p>
      <a href="../auth/signup_page.php" class="cta-btn">سجل مجانًا</a>
    </div>
    
    <div style="text-align: center;">
      <a href="../" class="back-btn">
        <i class="fas fa-arrow-left"></i> الرجوع للصفحة الرئيسية
      </a>
    </div>
  </div>

  <footer class="footer">
    <p>© 2023 منصة إعارة الأدوات الطبية. جميع الحقوق محفوظة.</p>
    <p>نسعى لتحقيق التكافل الصحي في المجتمع</p>
  </footer>

  <script>
    // Add animation to value cards on scroll
    const valueCards = document.querySelectorAll('.value-card');
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.animation = 'fadeInUp 0.8s ease forwards';
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });
    
    valueCards.forEach(card => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      observer.observe(card);
    });
    
    // Add keyframe animation
    const style = document.createElement('style');
    style.innerHTML = `
      @keyframes fadeInUp {
        from {
          opacity: 0;
          transform: translateY(20px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
    `;
    document.head.appendChild(style);
  </script>
</body>
</html>

 <style>
    :root {
      --primary: #2563eb;
      --primary-dark: #1d4ed8;
      --secondary: #10b981;
      --light: #f8fafc;
      --dark: #1e293b;
      --gray: #64748b;
      --light-gray: #e2e8f0;
      --white: #ffffff;
      --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Tajawal', sans-serif;
    }

    body {
      background: linear-gradient(to bottom, #f0f7ff, #e6f1ff);
      color: var(--dark);
      line-height: 1.8;
    }

    /* Hero Section */
    .about-hero {
background: 
  linear-gradient(rgba(37, 99, 235, 0.85), rgba(37, 99, 235, 0.85)),
  url('https://images.unsplash.com/photo-1586773860418-d37222d8fce3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1173&q=80');
background-position: center;
background-size: cover;
background-repeat: no-repeat;
      padding: 120px 20px 80px;
      color: var(--white);
      text-align: center;
      position: relative;
    }

    .about-hero h1 {
      font-size: 3.2rem;
      margin-bottom: 20px;
      font-weight: 800;
    }

    .about-hero p {
      font-size: 1.3rem;
      max-width: 800px;
      margin: 0 auto;
    }

    /* About Content */
    .about-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 80px 20px;
    }

    .about-section {
      background: var(--white);
      border-radius: 20px;
      box-shadow: var(--shadow);
      padding: 50px;
      margin-bottom: 60px;
      position: relative;
      overflow: hidden;
    }

    .about-section::before {
      content: "";
      position: absolute;
      top: 0;
      right: 0;
      width: 5px;
      height: 100%;
      background: linear-gradient(to bottom, var(--primary), var(--secondary));
    }

    .about-section h2 {
      font-size: 2.2rem;
      color: var(--primary);
      margin-bottom: 30px;
      position: relative;
      padding-bottom: 15px;
    }

    .about-section h2::after {
      content: "";
      position: absolute;
      bottom: 0;
      right: 0;
      width: 100px;
      height: 4px;
      background: linear-gradient(to right, var(--primary), var(--secondary));
      border-radius: 2px;
    }

    .about-section p {
      font-size: 1.2rem;
      color: var(--dark);
      margin-bottom: 25px;
      line-height: 1.9;
    }

    .quote {
      background: linear-gradient(to right, rgba(37, 99, 235, 0.05), rgba(16, 185, 129, 0.05));
      border-right: 4px solid var(--primary);
      padding: 30px;
      border-radius: 0 15px 15px 0;
      margin: 40px 0;
      position: relative;
    }

    .quote p {
      font-size: 1.4rem;
      font-style: italic;
      color: var(--primary-dark);
      font-weight: 600;
      margin-bottom: 15px;
    }

    .quote::before {
      content: "“";
      position: absolute;
      top: -20px;
      right: 20px;
      font-size: 6rem;
      color: rgba(37, 99, 235, 0.1);
      font-family: Georgia, serif;
      line-height: 1;
    }

    /* Values Section */
    .values-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
      margin-top: 50px;
    }

    .value-card {
      background: var(--white);
      border-radius: 15px;
      padding: 30px;
      box-shadow: var(--shadow);
      border-top: 4px solid var(--primary);
      transition: all 0.3s ease;
    }

    .value-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .value-card i {
      font-size: 2.5rem;
      color: var(--primary);
      margin-bottom: 20px;
    }

    .value-card h3 {
      font-size: 1.6rem;
      color: var(--primary-dark);
      margin-bottom: 15px;
    }

    .value-card p {
      color: var(--gray);
    }

    /* CTA */
    .cta-section {
      text-align: center;
      padding: 60px 20px;
      background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
      color: var(--white);
      margin-top: 50px;
      border-radius: 20px;
    }

    .cta-section h2 {
      font-size: 2.5rem;
      margin-bottom: 20px;
    }

    .cta-section p {
      font-size: 1.2rem;
      max-width: 700px;
      margin: 0 auto 30px;
    }

    .cta-btn {
      display: inline-block;
      padding: 16px 40px;
      background: var(--white);
      color: var(--primary);
      border-radius: 8px;
      text-decoration: none;
      font-weight: 700;
      font-size: 1.2rem;
      box-shadow: var(--shadow);
      transition: all 0.3s ease;
    }

    .cta-btn:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      background: rgba(255, 255, 255, 0.95);
    }

    .back-btn {
      display: inline-block;
      background: linear-gradient(to right, var(--primary), var(--secondary));
      color: var(--white);
      padding: 14px 35px;
      font-size: 1.2rem;
      text-decoration: none;
      border-radius: 8px;
      transition: all 0.3s ease;
      margin-top: 20px;
      box-shadow: var(--shadow);
      border: none;
      cursor: pointer;
      font-weight: 600;
    }

    .back-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 7px 15px rgba(0, 0, 0, 0.2);
      background: linear-gradient(to right, var(--primary-dark), #0d9488);
    }

    /* Footer */
    .footer {
      text-align: center;
      padding: 30px;
      color: var(--gray);
      font-size: 1rem;
      background: var(--white);
      margin-top: 80px;
      border-top: 1px solid var(--light-gray);
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
      .about-hero {
        padding: 90px 20px 60px;
      }
      
      .about-hero h1 {
        font-size: 2.2rem;
      }
      
      .about-hero p {
        font-size: 1.1rem;
      }
      
      .about-section {
        padding: 30px 20px;
      }
      
      .about-section h2 {
        font-size: 1.8rem;
      }
      
      .quote p {
        font-size: 1.2rem;
      }
      
      .cta-section h2 {
        font-size: 2rem;
      }
      
      .about-container {
        padding: 50px 20px;
      }
    }
  </style>