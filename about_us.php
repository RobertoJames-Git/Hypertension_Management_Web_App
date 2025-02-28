<?php
    /* Code by: Roberto James and Brandon Bent
     * 
     */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Mission</title>
    <!-- Link the external stylesheet -->
    <link rel="stylesheet" href="styles/aboutUsStyle.css">
</head>

<body>

    <?php
        require_once("navbar.php");
    ?>

    <section class="what-does-it-do">
        <div class="content-container">
            <div class="text-container">
                <h2>Who We Are</h2>
                <p>
                    Our research group is dedicated to exploring digital health solutions for hypertension management.
                    Through innovation, technology, and healthcare expertise, we aim to improve patient outcomes and
                    empower individuals to monitor and control their blood pressure effectively.
                </p>
                <p>
                    Our project focuses on the acceptability of digital health interventions among hypertensive patients
                    in Jamaica, analyzing how family and healthcare support impact user adoption. We strive to bridge
                    the gap between technology and healthcare to create meaningful solutions.
                </p>
            </div>
            <div class="image-container">
                <img src="Images/aboutUsImages//who-we-are-image.png" alt="What Does It Do">
            </div>
        </div>
    </section>

    <section class="mission-section" id="our-mission">
        <div class="mission-container">
            <div class="mission-image">
                <img src="Images/aboutUsImages/our-mission-image.png" alt="Our Mission">
            </div>
            <div class="mission-text">
                <h2>Our Mission</h2>
                <p>
                    Our mission is to improve hypertension management through innovative digital health interventions
                    that
                    empower patients, engage families, and enhance collaboration with healthcare professionals. By
                    integrating
                    user-friendly technology, we aim to support hypertensive patients in monitoring their blood
                    pressure,
                    adhering to treatment plans, and maintaining long-term health. Through a holistic approach that
                    combines
                    digital tracking, real-time alerts, and support networks, we strive to reduce the burden of
                    uncontrolled
                    hypertension and promote better health outcomes in Jamaica and beyond.
                </p>
            </div>
        </div>
    </section>

    <section class="our-inspiration">
        <div class="inspiration-container">
            <div class="inspiration-text">
                <h2>Our Inspiration</h2>
                <p>
                    Our mission is driven by the pressing need for accessible digital health solutions to
                    combat hypertension in Jamaica. We are inspired by the resilience of patients, the dedication of
                    healthcare providers, and the potential of technology to improve lives.
                </p>
                <p>
                    By embracing innovation, we seek to empower individuals with the tools they need to take
                    control of their health, fostering a future where managing hypertension is both simple and
                    effective.
                </p>
            </div>
            <div class="inspiration-image">
                <img src="Images/aboutUsImages/our-inspiration-image.png" alt="Our Inspiration">
            </div>
        </div>
    </section>

    <section class="how-it-works">
        <div class="content-container">
            <div class="text-container">
                <h2>How It All Works</h2>
                <p>
                    Our digital health platform is designed to empower hypertensive patients by providing an integrated
                    system for blood pressure tracking, family support, and healthcare collaboration.
                </p>
                <p>
                    By combining technology with professional and familial support, our platform helps patients take
                    control of their health while ensuring they receive the guidance and motivation needed to maintain
                    stable blood pressure levels.
                </p>
            </div>
            <div class="image-container">
                <img src="Images/aboutUsImages/image-how-it-works-image.png" alt="How It Works">
            </div>
        </div>
    </section>

    <section class="our-features">
        <div class="features-container">
            
            <!-- Alert Feature -->
            <div class="feature-item">
                <div class="feature-header">
                    <img src="Images/aboutUsImages/icon/alert-icon.svg" alt="Alert Icon">
                    <h2>Alert</h2>
                </div>
                <div class="feature-content">
                    <p><strong>Daily Reminders:</strong> Receive alerts to upload your blood pressure readings every day.</p>
                    <p><strong>Emergency Notifications:</strong> Instantly notify family members and healthcare professionals if your blood pressure is too high.</p>
                </div>
            </div>

            <!-- Monitor Feature -->
            <div class="feature-item">
                <div class="feature-header">
                    <img src="Images/aboutUsImages/icon/monitor-icon.svg" alt="Monitor Icon">
                    <h2>Monitor</h2>
                </div>
                <div class="feature-content">
                    <p><strong>Visual Tracking:</strong> View an interactive graph of your blood pressure readings.</p>
                    <p><strong>History Access:</strong> Easily access your past readings to monitor trends over time.</p>
                    <p><strong>Informed Decisions:</strong> Share your data with healthcare professionals for personalized care and improved management.</p>
                </div>
            </div>

            <!-- Support Feature -->
            <div class="feature-item">
                <div class="feature-header">
                    <img src="Images/aboutUsImages/icon/support-icon.svg" alt="Support Icon">
                    <h2>Support</h2>
                </div>
                <div class="feature-content">
                    <p><strong>Add Support Members:</strong> Include family members and healthcare professionals in your care team.</p>
                    <p><strong>Collaborative Care:</strong> Allow your support network to monitor your blood pressure readings and provide encouragement.</p>
                    <p><strong>Patient-Centered Messaging:</strong> Allow patients to communicate directly with their family members and healthcare professionals.</p>
                </div>
            </div>

        </div>
    </section>

    <section class="team-section">
        <h2 class="team-title">Meet Our Team</h2>
        <div class="team-container">

            <!-- Team Member 1 -->
            <div class="team-member">
                <div class="team-image">
                    <img src="Images/aboutUsImages/icon/team-member-icon.svg" alt="Roberto James">
                </div>
                <h3>Roberto James</h3>
                <p>Lead Researcher, Website Designer</p>
            </div>

            <!-- Team Member 2 -->
            <div class="team-member">
                <div class="team-image">
                    <img src="Images/aboutUsImages/icon/team-member-icon.svg" alt="Brandon Bent">
                </div>
                <h3>Brandon Bent</h3>
                <p>Researcher, Website Designer</p>
            </div>

            <!-- Team Member 3 -->
            <div class="team-member">
                <div class="team-image">
                    <img src="Images/aboutUsImages/icon/team-member-icon.svg" alt="Jhevon Noble">
                </div>
                <h3>Jhevon Noble</h3>
                <p>Researcher, QA Tester</p>
            </div>

            <!-- Team Member 4 -->
            <div class="team-member">
                <div class="team-image">
                    <img src="Images/aboutUsImages/icon/team-member-icon.svg" alt="Garvelle Fergison">
                </div>
                <h3>Garvelle Fergison</h3>
                <p>Researcher</p>
            </div>

        </div>
    </section>

    <section class="why-matter">
  <div class="why-matter-container">
    <div class="text-container">
    <h2>But...Why Does This Matter?</h2>
      <p>
                Hypertension is one of the most pressing health challenges in Jamaica, affecting thousands
                of individuals and their families. Managing high blood pressure requires consistent monitoring,
                lifestyle changes, and timely medical intervention. Unfortunately, many patients lack the necessary
                tools and support to do this effectively.
            </p>
            <p>
                Our digital health intervention aims to change this by providing an accessible, technology-driven
                solution that empowers patients, families, and healthcare providers. By making hypertension management
                more efficient and user-friendly, we can improve health outcomes, reduce hospital visits, and enhance
                the overall well-being of our communities.
            </p>
    </div>
    <div class="image-container">
      <img src="Images/aboutUsImages/why-this-matters.png" alt="Descriptive alt text">
    </div>
  </div>
</section>

    <!-- Future Vision Section -->
    <section class="why-does-this-happen">
  <div class="background-overlay"></div>
  <div class="future-container">
    <div class="text-container">
      <h2>Future Vision</h2>
      <p>We are committed to continuous improvement, ensuring that our digital health platform evolves to meet the
        needs of hypertension patients.</p>
      <ul>
        <li><strong>AI-Powered Health Insights:</strong> Advanced machine learning to offer personalized recommendations.</li>
        <li><strong>Wearable Device Integration:</strong> Real-time monitoring via smartwatches and fitness trackers.</li>
        <li><strong>Predictive Alerts:</strong> AI-driven risk detection for early intervention.</li>
        <li><strong>Enhanced Telemedicine Support:</strong> Seamless connections between patients and healthcare providers.</li>
      </ul>
      <p>By leveraging innovative technologies, we aim to revolutionize hypertension management and improve patient outcomes.</p>
    </div>
    <div class="image-container">
      <img src="Images/aboutUsImages/future-image.png" alt="Future Vision Image">
    </div>
  </div>
</section>

    <section class="contact-us-section">
        <div class="contact-header">
            <h1>Contact Us</h1>
            <p class="subheading">Get the info you're looking for right now</p>
        </div>

        <div class="contact-containers">
            <!-- Container 1 -->
            <div class="contact-box">
                <h3>Contact</h3>
                <div class="contact-details">
                    <p>
                        Dr. Nadine Maitland,<br>
                        Faculty of Engineering and Computing Ethics Coordinator<br>
                        University of Technology, Jamaica<br>
                        Email: <a href="mailto:nmaitland@utech.edu.jm">nmaitland@utech.edu.jm</a>
                    </p>
                    <p>
                        Mr. A. Lawrence,<br>
                        Data Protection Officer<br>
                        Email: <a href="mailto:dpo@utech.edu.jm">dpo@utech.edu.jm</a>
                    </p>
                </div>
            </div>

            <!-- Container 2 -->
            <div class="contact-box">
                <h3>Principal Supervisor</h3>
                <div class="contact-details">
                    <p>
                        Ms. Susan Muir<br>
                        Associate Professor<br>
                        237 Old Hope Road, Kingston<br>
                        1876-927-1680-8<br>
                        Email: <a href="mailto:smuir@utech.edu.jm">smuir@utech.edu.jm</a>
                    </p>
                </div>
            </div>

            <!-- Container 3 -->
            <div class="contact-box">
                <h3>The Researchers</h3>
                <div class="contact-details">
                    <p>
                        Email:<br>
                        <a href="mailto:majorprojectMP05health@gmail.com">majorprojectMP05health@gmail.com</a><br>
                        <a href="mailto:majorprojectMP05health@outlook.com">majorprojectMP05health@outlook.com</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

</body>

</html>