describe('Login Test', () => {
    it('should log in successfully', () => {
      cy.visit('http://localhost:8000/login.php');  // Az alkalmazásod login oldalát látogatja meg
      cy.get('input[name=email]').type('a@a.com');  // Kitölti az email mezőt
      cy.get('input[name=password]').type('a');  // Kitölti a jelszó mezőt
      cy.get('button[type=submit]').click();  // Rákattint a bejelentkezés gombra
      cy.url().should('include', '/index.php');  // Ellenőrzi, hogy az index.php-ra jutott
    });
  });
  