lectio
======
Lectio API
This is a free API For Lectio that everybody can use as they will. No guarentees, no expectations.
Most of this is in Danish, because Lectio is a Danish system. Please contact me if you need to 
discuss this in English.

Danish text from here:

Dette er et simpelt API for Lectio som tillader at hente alle nyttige offentlige data og på sigt
også data når man er logget ind (f.eks. beskeder og vedhæftninger samt fravær).
Der er et lille "underprojekt" som måske egentlig ikke hører så meget hjemme her,
kaldet "isitagirl". Det har sin egen class da det på ingen måde skal kobles tæt 
med Lectio da de færreste skal bruge det, men grundet at vi havde lidt sjov
med at trække alle pigerne ud af gymnasiets elevliste så er det inkluderet.

Selve API'et fungerer som følger.

Liste over funktioner:

Alle de her bør sige sig selv:
	get_skema_til_elev(gymnasiekode, lectio_id)
	get_skema_til_elev_og_uge(gymnasiekode, lectio_id, ugekode)
	get_skema_til_laerer(gymnasiekode, laerer_id)
	get_skema_til_laerer_og_uge(gymnasiekode, laerer_id, ugekode)

Disse funktioner hiver elever ud fra et givent gymnasie.
get_elever_fra_gymnasie er den lidt simple version, mens den "sorterede" version
kategoriserer eleverne efter klasse og trækker elev nummer (kursist nummer) med ud.
BEMÆRK at get_elever_fra_gymnasie_sorteret ikke garanterer alfabetisk sortering.
	get_elever_fra_gymnasie(gymsiekode)
	get_elever_fra_gymnasie_sorteret(gymnasiekode)

Denne funktion henter alle lærerne ud med navn og initialer samt laerer_id til brug ved andre funktioner
	get_laerere(gymnasiekode)

De her funktioner får du nok ikke brug for, de tillader at hente et skema fra en hvilken som helst URL
man selv konstruerer, samt at hente elever fra en given side hvis man f.eks. kun vil have elever
hvis navn begynder med B.
	get_skema(url til skemaet)
	get_elever_fra_side(url til elevsiden)


Forklaringer på parametre:
"Gymnasiekode" er den talkode hvert gymnasie har. Den kan ses i toppen af url'en når man er på en 
vilkårlig side på et gymnasie. I en af de næste commits vil der komme funktioner til at finde disse koder.
Men feks. her er Nakskov Gymnasiums URL:
	http://www.lectio.dk/lectio/402/default.aspx
402 er gymnasiekoden i dette tilfælde.

"Lectio_id" referer til det ID som Lectio tilegner hver elev. Det er for at adskille fra Elev nummer som
er det tal skolen tildeler hver elev i hver klasse.

"Ugekode" er ret vigtig. Den skal indsættes som WWYYYY dvs. for uge 11 i år 2013 så er koden 112013