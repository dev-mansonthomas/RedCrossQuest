# 🏆 RedCrossQuest

![GitHub stars](https://img.shields.io/github/stars/dev-mansonthomas/RedCrossQuest?style=social)
![GitHub issues](https://img.shields.io/github/issues/dev-mansonthomas/RedCrossQuest)
![GitHub license](https://img.shields.io/github/license/dev-mansonthomas/RedCrossQuest)
![Contributors](https://img.shields.io/github/contributors/dev-mansonthomas/RedCrossQuest)

![PHP](https://img.shields.io/badge/PHP-8.3-blue?logo=php)
![Slim Framework](https://img.shields.io/badge/Slim_Framework-4-green?logo=php)
![Angular 1.7](https://img.shields.io/badge/Angular-1.7-red?logo=angular)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange?logo=mysql)
![Google App Engine](https://img.shields.io/badge/Google_App_Engine-F8C200?logo=google-cloud&logoColor=white)
![Firebase](https://img.shields.io/badge/Firebase-Cloud-orange?logo=firebase)
![Google PubSub](https://img.shields.io/badge/Google_PubSub-F8C200?logo=google-cloud&logoColor=white)
![Google Cloud Monitoring](https://img.shields.io/badge/Google_Cloud_Monitoring-4285F4?logo=google-cloud&logoColor=white)
![Google Cloud Functions](https://img.shields.io/badge/Google_Cloud_Functions-4285F4?logo=google-cloud&logoColor=white)

**RedCrossQuest** is a web application developed to ease the management of the French Red Cross yearly fundraising. 
This event, which lasts only 9 days each year around May/June, is critical for local Red Cross Units as it accounts for 25% to 50% of their annual budget. 
It is also challenging in terms of application development, monitoring, bug resolution, and implementing last-minute feature requests.

## 🚀 Main Features

The web application allows to:
- **Manage volunteers**: whether they are Red Cross Volunteers or one-day volunteers.
- **Manage collections**: Determine where they should be collecting money (assisted by Business Intelligence analytics), when they start and finish collecting, and track the locations of the collections.
- **QR Code scanning**: Used to identify a bucket of money, a volunteer, or to register volunteers in RedQuest.
- **Rewarding Volunteers**: Track the amount of money collected per volunteer with statistics that enable us to thank them in various ways, ensuring everyone is appreciated in a positive manner.
- **Deep understanding of performance**: Monitor the amount collected versus the objectives, including the number of volunteers, hours of collecting, etc. This helps forecast outcomes using BI.
- **Working with Banks**: Facilitate money collection by banks. Money is placed in bags that must not be overweight (or a penalty will be applied) and details the number of coins and bills.
- **Data analysis and quality**: Numerous dynamic TIBCO Spotfire dashboards for analyzing the fundraising, with links back to the application to correct any mistakes.
- **Gamification**: RedQuest is a supplementary web application based on Firebase that uses various badges to encourage volunteers to collect more money, funding more actions in the year ahead.

### Application screenshots

<details>
    <summary>Login and miscellaneous screens</summary>
    <p>Login page</p>
    <img src="README/login/login_page.png" alt="login page"/>
    <p>Welcome page</p>
    <img src="README/login/welcome_page.png" alt="Welcome page"/>
    <p>Embedded Tutorial to setup the application for a new unit</p>
    <img src="README/login/embedded_tutorial.png" alt="Embedded Tutorial to setup the application for a new unit"/>
    <p>Support page: what to transmit, where to open a ticket, new features</p>
    <img src="README/login/support.png" alt="Support page: what to transmit, where to open a ticket, new features"/>
</details>

<details>
    <summary>Main pages</summary>
    <p>QR Code scanning to automatically fill search field without typo</p>
    <img src="README/main/scan_QR_code.png" alt="QR Code scanning to automatically fill search field without typo"/>
    <p>Once collecting is done, count the money: coins and bills</p>
    <img src="README/main/tronc_queteur_1.png" alt="Once collecting is done, count the money: coins and bills"/>
    <p>Credit Card and bank notes</p>
    <img src="README/main/tronc_queteur_2.png" alt="Credit Card and bank notes"/>
    <p>Bank money bag assignment and free notes</p>
    <img src="README/main/tronc_queteur_3.png" alt="Bank money bag assignment and free notes"/>
</details>

<details>
    <summary>Administration</summary>
    <p>Collecting location editor</p>
    <img src="README/admin/point_quete.png" alt="Collecting location editor"/>
    <p>Unit parameters editor</p>
    <img src="README/admin/ul_parameters.png" alt="Unit parameters editor"/>
    <p>Volunteer list</p>
    <img src="README/admin/volunteer_list.png" alt="Volunteer list"/>
    <p>Volunteer editor</p>
    <img src="README/admin/volunteer_editor.png" alt="Volunteer editor"/>
    <p>QR Code printing (one of the 3 ones)</p>
    <img src="README/admin/qr_code_printing.png" alt="QR Code printing (one of the 3 ones)"/>
    <p>Manual input of data before the unit uses RedCrossQuest to provide statistics references</p>
    <img src="README/admin/historic_before_using_rcq.png" alt="Manual input of data before the unit uses RedCrossQuest to provide statistics references"/>
    <p>Objectives interface</p>
    <img src="README/admin/objectifs.png" alt="Objectives interface"/>
    <p>Mailing interface that sends emails with TIBCO Spotfire displaying their accomplishments</p>
    <img src="README/admin/mailing.png" alt="Mailing interface that sends emails with TIBCO Spotfire displaying their accomplishments"/>
    <p>GDPR Export of unit data</p>
    <img src="README/admin/export_rgpd.png" alt="GDPR Export of unit data"/>
</details>

<details>
    <summary>TIBCO Spotfire dashboards</summary>
    <p>KPI over the years</p>
    <img src="README/dashboards/graph_kpi.png" alt="KPI over the years"/>
    <p>How the unit performs compared to its objective</p>
    <img src="README/dashboards/graph_objective_vs_accomplished.png" alt="How the unit performs compared to its objective"/>
    <p>How did the unit perform in the past compared to the current year</p>
    <img src="README/dashboards/graph_year_by_year.png" alt="How did the unit perform in the past compared to the current year"/>
    <p>Data quality check: spot input errors (in amount or timing) with the ability to edit the particular row that has an issue directly in the web application</p>
    <img src="README/dashboards/data_quality_control.png" alt="Data quality check: spot input errors (in amount or timing) with the ability to edit the particular row that has an issue directly in the web application"/>
</details>

## 🛠️ Technologies

- **Backend:** Google App Engine (PHP) with Slim Framework
- **Databases:** Google Cloud SQL (MySQL), Firestore
- **Frontend:** Angular 1.7 (migration needed to the latest version of Angular).
- **BI & Analytics:** TIBCO Spotfire, considering a migration to Looker/Google Data Studio.
- **Others:** Google Pub/Sub, Cloud Functions, Stackdriver, Sendgrid, Slack, ReCaptcha.

## 📈 Statistics since 2016

- 👥 **9 500** volunteers (Red Cross & one-day volunteers).
- ⏳ **125 700** hours of collecting money.
- 💰 **3 750 000€** collected in donations.
- 🏋🏽 **20,14 metric tons** of coins & bills.
- 🏘️ **+65** local units using RedCrossQuest.

## 🏅 Gamification: [RedQuest](https://github.com/dev-mansonthomas/RedQuest)

To motivate volunteers, RedQuest grants badges and levels based on their accomplishments:
- % of the local unit objective they've collected (€)
- Amount of money collected via Credit Card
- Amount of weight (kg) in bills and coins collected
- **Number of days collected:** Number of days the volunteer spent collecting donations.
- **Number of locations collected:** Number of collection points where the volunteer collected donations.
- **Number of times they went out collecting:** Number of times the volunteer went out for a collection session (they can have multiple sessions in a day, e.g., two 2-hour slots).
- **Number of hours collected:** Total time spent collecting donations.


Each volunteer can see their progress in the RedQuest app, how much they have collected each time, and their ranking (if the feature is enabled by their unit). 
Beyond gamification, it also allows volunteers to be thanked at the end of the collection.

## 📚 Documentation

The documentation is available in the French Red Cross information system and is not public.

## 💡 Contribute

We are looking for developers:
- Migrate the frontend to the latest version of Angular
- Add new features to both frontend and backend
- Enhance the gamification (like supporting teams, national ranking with rewards)

**Wanted Skills:**
- Angular 10+, Firebase, Cloud Functions.
- PHP, Python, Typescript.
- Google Data Studio or Looker.

## 📝 License

This project is licensed under GPL v3. You can read the license here: [LICENSE](LICENSE).

## 📬 Contact

If you have any questions or suggestions, or if you want to contribute, please open a GitHub issue. 🙂
