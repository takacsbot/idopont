# Firestarter Akadémia Weboldal 🔥

## Projekt Áttekintés
A Firestarter Akadémia egy modern, reszponzív weboldal, amely PHP backendre és MySQL adatbázisra épül. A rendszer célja, hogy online platformot biztosítson az akadémia szolgáltatásainak (pl. coaching, tréningek, workshopok) bemutatására, interaktív időpontfoglalásra, valamint a felhasználók és a foglalások hatékony adminisztrációjára.

## Tartalomjegyzék
- [Főbb Funkciók](#főbb-funkciók)
- [Technikai Részletek](#technikai-részletek)
- [Előfeltételek](#előfeltételek)
- [Telepítés](#telepítés)
- [Futtatás](#futtatás)
- [Használat](#használat)
- [Kapcsolat](#kapcsolat)

## Főbb Funkciók
### 👤 Felhasználóknak
-   **Regisztráció:** E-maillel vagy Google fiókkal.
-   **Elfelejtett jelszó:** Új jelszó kérése e-mailben.
-   **Profil:** Saját adatok szerkesztése.

### 📅 Időpontfoglalás
-   **Naptár:** Szabad időpontok mutatása és választása.
-   **Foglalás:** Könnyű időpontfoglalás.

### 🎨 Kinézet
-   **Reszponzív:** Jól néz ki mobilon és gépen is.
-   **Témák:** Választható sötét és világos mód.
-   **Animációk:** Látványos elemek az [AOS Library(https://michalsnik.github.io/aos/) segítségével.
-   **Dizájn:** Modern, színátmenetes megjelenés.

### 👨‍💼 Adminoknak
-   **Felhasználók kezelése:** Lista, keresés (név/e-mail), adatok módosítása, törlés.

### 🧑‍🏫 Oktatóknak
-   **Szolgáltatások kezelése:** Új szolgáltatás felvétele, szerkesztése, törlése.
-   **Foglalások megtekintése:** Az oktató látja a hozzá tartozó foglalásokat/órarendet.

## Technikai Részletek
-   **Backend:** PHP
-   **Adatbázis:** MySQL
-   **Frontend:** HTML, CSS, JavaScript
-   **Autentikáció:** Saját implementáció, Google OAuth

## Előfeltételek
A projekt futtatásához a következőkre van szükség:
-   PHP (ajánlott verzió: 8.2.12)
-   MySQL adatbázisszerver
-   Webszerver (pl. Apache, Nginx) PHP támogatással
-   Git (verziókezeléshez)

## Telepítés
1.  **Klónozd a repository-t:**
    ```bash
    git clone https://github.com/takacsbot/idopont
    cd idopont
    ```
2.  **Telepítsd a PHP függőségeket:**
    ```bash
    composer install
    ```
3.  **Hozz létre egy adatbázist** a MySQL szervereden (`timetable_db`).
4.  **Konfiguráld a környezeti változókat vagy a konfigurációs fájlt:**
    -   Keresd meg a projekt konfigurációs fájlját (`php_backend/db_config.php`).
    -   Szerkeszd a db_config fájlt, és add meg az adatbázis kapcsolati adatait (host, port, adatbázis neve, felhasználónév, jelszó).
5.  **Importáld az adatbázist**
    -   Keresd meg az SQL fájlokat (`timetable_db.sql`).
    -   Importáld őket a létrehozott adatbázisba.

## Futtatás
Navigálj a projekt gyökérkönyvtárába a terminálban, és futtasd:
```bash
php -S localhost:8000 -t .
```
Ezután az oldal elérhető lesz a `http://localhost:8000` címen.

## Használat
A weboldal a beállított URL-en érhető el. A felhasználók regisztrálhatnak, bejelentkezhetnek, böngészhetnek a szolgáltatások között, és időpontokat foglalhatnak. Az adminisztrátorok és oktatók a bejelentkezés után férnek hozzá a speciális kezelőfelületeikhez.

## Teszt felhasználók
- **Admin:** `a@a.com` || `a`
- **Oktatók:**
    - `peter@firestarter.hu` || `kovacspeter`
    - `anna@firestarter.hu` || `nagyanna`
    - `janos@firestarter.hu` || `kissjanos`

## Kapcsolat
Ha kérdésed vagy javaslatod van, lépj kapcsolatba a fejlesztőkkel:

### 👨‍💼 Fejlesztők
> - Balogh Richárd
> - Takács Botond