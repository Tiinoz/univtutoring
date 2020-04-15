# **Moodle 3.8+ Plugin pour le tutorat**


Description 
===========

C'est un projet réalisé dans le cadre du developpement du tutorat sur moodle pour l'université de Bordeaux. 

C'est une activité moodle gérant des événements sur un calendrier.
Il permet aux éléves de prendre rendez-vous directement sous moodle, en plus de faciliter la communication entre le responsable et les tuteurs.
Le plugin est effectué avec [Fullcalendar](https://fullcalendar.io/) qui est en OpenSource.

  
Installation
===========

### **Dans les paramètres moodle (site)**

`Site Administration` -> `Plugins` -> `Install plugins` -> **Drag** and **Drop** the **ZIP** file <br>

`Show More` -> 
**Plugin Type** : mod   <br>

`Install plugin from the ZIP file`


### **Dans le dossier moodle**

Pour installer le plugin en utilisant git, exécutez la commande suivante en étant à la racine de votre moodle :
```bash
git clone https://services.emi.u-bordeaux.fr/projet/git/moodlee ../mod/univtutoring
``` 
Ou, extraire le zip dans `racine_moodle/mod/`:
```bash
cd racine_moodle/mod/
wget https://github.com/Tiinoz/moodle-mod_univtutoring/archive/master.zip
unzip -j master.zip -d univtutoring
```

Ressources
===========

+ [FN Readme](resources/fr/README.MD)
+ [FR User Guide](resources/fr/guide.md)

Auteurs et contributeurs
===========

In 2020, ***Lucas DECLERCQ***, ***Guillaume LIBET***, ***Enzo FAUTRAT***, ***Angelika COÏC***, ***Zaki YASSINE*** et ***Zoé DEBATTY*** basé sur FullCalendar.

