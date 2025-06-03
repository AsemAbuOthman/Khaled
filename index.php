<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>منصة إعارة الأدوات الطبية</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
</head>
<body>
  <?php include("../Medical-System/main/partials/header-1.php"); ?>

  <div class="hero">
    <div class="hero-content">
      <div class="hero-text">
        <h1>مرحبًا بك في منصة إعارة الأدوات الطبية</h1>
        <p>منصة إلكترونية تهدف لربط المحتاجين بالأدوات الطبية مع المعيرين لتقليل التكاليف وتحقيق التكافل الصحي في المجتمع.</p>
        <div class="hero-buttons">
          <a href="/Medical-System/auth/signup_page.php" class="btn primary">انضم إلينا الآن</a>
          <a href="/Medical-System/main/about.php" class="btn secondary">تعرف على المزيد</a>
        </div>
      </div>
      <div class="hero-image">
        <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Medical Equipment">
      </div>
    </div>
  </div>

  <div class="features">
    <div class="feature">
      <i class="fas fa-hand-holding-medical"></i>
      <h3>توفير الأدوات</h3>
      <p>وفر الأدوات الطبية التي لا تستخدمها لمساعدة المحتاجين</p>
    </div>
    <div class="feature">
      <i class="fas fa-handshake"></i>
      <h3>تكافل مجتمعي</h3>
      <p>ساهم في بناء مجتمع صحي متكافل يخدم بعضه البعض</p>
    </div>
    <div class="feature">
      <i class="fas fa-money-bill-wave"></i>
      <h3>توفير التكاليف</h3>
      <p>وفر على المرضى تكاليف شراء الأدوات الطبية الباهظة</p>
    </div>
  </div>

  <div class="cards-section">
    <h2>كيف تعمل المنصة؟</h2>
    <div class="cards-container">
      <div class="card">
        <div class="card-icon">
          <i class="fas fa-user-injured"></i>
        </div>
        <h3>👨‍⚕️ للمرضى</h3>
        <p>احصل على الأدوات الطبية التي تحتاجها مجانًا أو برسوم رمزية، من خلال المعيرين المتطوعين.</p>
        <a href="/Medical-System/auth/signup_page.php?role=user" class="card-btn">سجل كمستفيد</a>
      </div>
      <div class="card">
        <div class="card-icon">
          <i class="fas fa-hospital-user"></i>
        </div>
        <h3>🏥 للمعيرين</h3>
        <p>ساهم في خدمة مجتمعك بإعارة الأدوات التي لا تستخدمها، وساعد من هم بحاجة إليها.</p>
        <a href="/Medical-System/auth/signup_page.php?role=lender" class="card-btn">سجل كمعير</a>
      </div>
    </div>
  </div>

  <div class="testimonials">
    <h2>ما يقولون عنا</h2>
    <div class="testimonials-container">
      <div class="testimonial">
        <div class="testimonial-content">
          <i class="fas fa-quote-left"></i>
          <p>ساعدتني المنصة في الحصول على كرسي متحرك بعد عملية جراحية، وفرت علي مبلغًا كبيرًا كنت سأدفعه لشراء كرسي جديد</p>
          <div class="testimonial-author">
            <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="سارة محمد">
            <div>
              <h4>سارة محمد</h4>
              <p>مستفيدة</p>
            </div>
          </div>
        </div>
      </div>
      <div class="testimonial">
        <div class="testimonial-content">
          <i class="fas fa-quote-left"></i>
          <p>أنا سعيد جدًا بمشاركتي في المنصة، حيث أستطيع مساعدة الآخرين بأدواتي الطبية التي لم أعد أستخدمها</p>
          <div class="testimonial-author">
            <img src="https://randomuser.me/api/portraits/men/54.jpg" alt="أحمد خالد">
            <div>
              <h4> الشيخ أحمد خالد</h4>
              <p>معير</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="stats">
    <div class="stat">
      <h3>500+</h3>
      <p>أداة متاحة</p>
    </div>
    <div class="stat">
      <h3>1200+</h3>
      <p>مستفيد</p>
    </div>
    <div class="stat">
      <h3>350+</h3>
      <p>معير</p>
    </div>
    <div class="stat">
      <h3>98%</h3>
      <p>رضا العملاء</p>
    </div>
  </div>

  <div class="cta">
    <h2>انضم إلى مجتمعنا الصحي المتكافل</h2>
    <p>سجل الآن وابدأ رحلتك معنا سواء كنت مستفيدًا تبحث عن أداة طبية أو معيرًا ترغب في مساعدة الآخرين</p>
    <a href="../Medical-System/auth/signup_page.php" class="cta-btn">سجل مجانًا</a>
  </div>

  <?php include("../Medical-System/main/partials/footer.php"); ?>

  <script>
    // Simple animation for stats
    const stats = document.querySelectorAll('.stat h3');
    stats.forEach(stat => {
      const target = parseInt(stat.textContent);
      let count = 0;
      const increment = target / 50;
      
      const updateCount = () => {
        if (count < target) {
          count += increment;
          stat.textContent = Math.ceil(count) + (stat.textContent.includes('%') ? '%' : '+');
          setTimeout(updateCount, 30);
        } else {
          stat.textContent = target + (stat.textContent.includes('%') ? '%' : '+');
        }
      };
      
      updateCount();
    });
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
  background-color: #f0f7ff;
  color: var(--dark);
  line-height: 1.6;
}

/* Hero Section */
.hero {
  background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
  padding: 60px 20px;
  color: var(--white);
}

.hero-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 40px;
}

.hero-text {
  flex: 1;
  min-width: 300px;
}

.hero h1 {
  font-size: 2.8rem;
  margin-bottom: 20px;
  font-weight: 800;
}

.hero p {
  font-size: 1.2rem;
  margin-bottom: 30px;
  max-width: 600px;
}

.hero-buttons {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.btn {
  padding: 14px 28px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1.1rem;
  text-decoration: none;
  transition: all 0.3s ease;
  display: inline-block;
}

.btn.primary {
  background-color: var(--white);
  color: var(--primary-dark);
  box-shadow: var(--shadow);
}

.btn.primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
}

.btn.secondary {
  background-color: transparent;
  color: var(--white);
  border: 2px solid var(--white);
}

.btn.secondary:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.hero-image {
  flex: 1;
  min-width: 300px;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.hero-image img {
  width: 100%;
  height: auto;
  display: block;
  transition: transform 0.5s ease;
}

.hero-image:hover img {
  transform: scale(1.05);
}

/* Features */
.features {
  max-width: 1200px;
  margin: 80px auto;
  padding: 0 20px;
  display: flex;
  justify-content: center;
  gap: 30px;
  flex-wrap: wrap;
}

.feature {
  text-align: center;
  max-width: 300px;
  padding: 30px;
  background: var(--white);
  border-radius: 15px;
  box-shadow: var(--shadow);
  transition: all 0.3s ease;
}

.feature:hover {
  transform: translateY(-10px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.feature i {
  font-size: 3rem;
  color: var(--primary);
  margin-bottom: 20px;
}

.feature h3 {
  font-size: 1.5rem;
  margin-bottom: 15px;
  color: var(--dark);
}

.feature p {
  color: var(--gray);
}

/* Cards Section */
.cards-section {
  background: linear-gradient(to bottom, #f0f7ff, #e6f1ff);
  padding: 80px 20px;
}

.cards-section h2 {
  text-align: center;
  font-size: 2.2rem;
  margin-bottom: 50px;
  color: var(--primary-dark);
}

.cards-container {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: center;
  gap: 40px;
  flex-wrap: wrap;
}

.card {
  background: var(--white);
  border-radius: 15px;
  box-shadow: var(--shadow);
  padding: 40px 30px;
  max-width: 400px;
  text-align: center;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.card:hover {
  transform: translateY(-10px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
}

.card-icon {
  width: 80px;
  height: 80px;
  background: rgba(37, 99, 235, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 25px;
  font-size: 2rem;
  color: var(--primary);
}

.card h3 {
  font-size: 1.6rem;
  margin-bottom: 20px;
  color: var(--primary-dark);
}

.card p {
  color: var(--gray);
  margin-bottom: 30px;
  font-size: 1.1rem;
}

.card-btn {
  display: inline-block;
  padding: 12px 30px;
  background: var(--primary);
  color: var(--white);
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
}

.card-btn:hover {
  background: var(--primary-dark);
  transform: translateY(-3px);
}

/* Testimonials */
.testimonials {
  padding: 80px 20px;
  background: var(--white);
}

.testimonials h2 {
  text-align: center;
  font-size: 2.2rem;
  margin-bottom: 50px;
  color: var(--primary-dark);
}

.testimonials-container {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: center;
  gap: 30px;
  flex-wrap: wrap;
}

.testimonial {
  max-width: 500px;
  background: var(--light);
  border-radius: 15px;
  padding: 30px;
  box-shadow: var(--shadow);
  position: relative;
}

.testimonial-content {
  position: relative;
}

.testimonial-content i {
  position: absolute;
  top: -20px;
  right: -10px;
  font-size: 4rem;
  color: rgba(37, 99, 235, 0.1);
  z-index: 0;
}

.testimonial p {
  font-size: 1.1rem;
  color: var(--dark);
  margin-bottom: 30px;
  position: relative;
  z-index: 1;
}

.testimonial-author {
  display: flex;
  align-items: center;
  gap: 15px;
}

.testimonial-author img {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
}

.testimonial-author h4 {
  font-size: 1.2rem;
  color: var(--primary-dark);
}

.testimonial-author p {
  color: var(--gray);
  margin: 0;
}

/* Stats */
.stats {
  background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
  padding: 60px 20px;
  color: var(--white);
}

.stats-container {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-around;
  flex-wrap: wrap;
  gap: 30px;
}

.stat {
  text-align: center;
  min-width: 200px;
}

.stat h3 {
  font-size: 3rem;
  margin-bottom: 10px;
  font-weight: 700;
}

.stat p {
  font-size: 1.2rem;
}

/* CTA */
.cta {
  padding: 80px 20px;
  text-align: center;
  background: url('https://images.unsplash.com/photo-1586773860418-d37222d8fce3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1173&q=80') center/cover no-repeat;
  position: relative;
  color: var(--white);
}

.cta::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(37, 99, 235, 0.85);
}

.cta > * {
  position: relative;
  z-index: 1;
}

.cta h2 {
  font-size: 2.5rem;
  margin-bottom: 20px;
}

.cta p {
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

/* Responsive Styles */
@media (max-width: 768px) {
  .hero h1 {
    font-size: 2.2rem;
  }
  
  .hero-content {
    flex-direction: column;
  }
  
  .hero-text, .hero-image {
    width: 100%;
  }
  
  .hero-buttons {
    justify-content: center;
  }
  
  .cards-container, .testimonials-container {
    flex-direction: column;
    align-items: center;
  }
  
  .stat {
    min-width: 150px;
  }
}
</style>