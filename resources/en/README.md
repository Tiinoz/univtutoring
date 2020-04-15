# **Moodle 3.8+ plugin for univtutoring**


Synthesis
===========

C'est un projet réalisé dans le cadre du developpement du tutorat sur moodle pour l'université de Bordeaux. 

C'est une activité moodle gérant des événements sur un calendrier.
Il permet aux éléves de prendre rendez-vous directement sous moodle, en plus de faciliter la communication entre le responsable et les tuteurs.
Le plugin est effectué avec [Fullcalendar](https://fullcalendar.io/) qui est en OpenSource.

Installation
===========

### **In moodle settings**

`Site Administration` -> `Plugins` -> `Install plugins` -> **Drag** and **Drop** the **ZIP** file <br>

`Show More` -> 
**Plugin Type** : mod   <br>

`Install plugin from the ZIP file`


### **In moodle directory**

To install the plugin using git, execute the following commands in the root of your Moodle install :
```bash
git clone https://services.emi.u-bordeaux.fr/projet/git/moodlee your_moodle_root/mod/univtutoring
```
Or, extract the following zip in `your_moodle_root/mod/`:
```bash
cd your_moodle_root/mod/
wget https://github.com/Tiinoz/moodle-mod_univtutoring/archive/master.zip
unzip -j master.zip -d univtutoring
```

Resources
===========

+ [FN Readme](resources/fr/README.MD)
+ [FR User Guide](resources/fr/guide.md)

Authors and Contributors
===========

In 2020, ***Lucas DECLERCQ***, ***Guillaume LIBET***, ***Enzo FAUTRAT***, ***Angelika COÏC***, ***Zaki YASSINE***, ***Zoé DEBATTY*** based on FullCalendar.
