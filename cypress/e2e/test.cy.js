import 'cypress-file-upload'
const login = (email, password) => {
  cy.session([email, password], () => {
    cy.visit('http://localhost:8000/login.php');
    cy.get('input[name=email]').type(email);
    cy.get('input[name=password]').type(password);
    cy.get('button[type=submit]').click();
    cy.visit('http://localhost:8000/index.php');
    cy.url().should('include', '/index.php');
  });
};

describe('User Registration Test', () => {
  it('should register a new account', () => {
    cy.visit('http://localhost:8000/registration.php');
    cy.get('input[name=username]').type('TestUser');
    cy.get('input[name=email]').type('test@example.com');
    cy.get('input[type=password]').type('password123');
    cy.get('button[type=submit]').click();
    cy.url().should('include', '/registration.php');
    cy.get('p').should('contain', 'Regisztráció sikeres!');
  });
})

describe('Login Tests', () => {
  beforeEach(() => {
    cy.clearCookies();
  });

  it('should log in as regular user', () => {
    login('a@a.com', 'a');
  });

  it('should log in as instructor', () => {
    login('peter@firestarter.hu', 'kovacspeter');
  });
});

describe('Instructor Panel Test', () => {
  beforeEach(() => {
    cy.clearCookies();
    login('peter@firestarter.hu', 'kovacspeter');
  });

  it('should create a service', () => {
    cy.visit('http://localhost:8000/foglalas.php');
    cy.get('#services > form > input[type=text]:nth-child(1)')
      .should('be.visible')
      .type('Test Service');
    cy.get('input[name=description]').type('Test Description');
    cy.get('input[name=recommended_time]').type('60');
    cy.get('input[name=recommended_to]').type('Everyone');
    cy.get('input[name=duration]').type('45');
    cy.get('input[name=price]').type('5000');
    cy.get('input[name=image]').attachFile('teszt.jpg');

    cy.get('#services > form > button').click();
    cy.url().should('include', '/foglalas.php');
    cy.get('#alert').should('contain', 'Szolgáltatás sikeresen hozzáadva!')
  });
  it('should delete the service', () => {
    cy.visit('http://localhost:8000/foglalas.php');
    cy.get('tbody > tr')
      .contains('td', 'Test Service')
      .parent('tr')
      .find('td:last-child button:last-child')
      .click();
    cy.url().should('include', '/foglalas.php');
    cy.get('tbody').should('not.contain', 'Test Service');
  });
});



describe('Admin Panel Test', () => {
    beforeEach(() => {
      cy.clearCookies();
      login('a@a.com', 'a');
    });
    it('should give the test account instructor permissions', () => {
      cy.visit('http://localhost:8000/admin.php');
      cy.get('tbody > tr').contains('td', 'TestUser').parent('tr')
          .find('td[data-label="Műveletek"] > .admin-actions > :nth-child(3) > .instructor-toggle')
          .click();
      cy.reload();
      cy.get('tbody > tr')
          .contains('td', 'TestUser')
          .parent('tr')
          .find('td[data-label="Oktató"]')
          .should('contain', 'Igen');
  });

  it('should take away the test account instructor permissions', () => {
      cy.visit('http://localhost:8000/admin.php');
      cy.get('tbody > tr').contains('td', 'TestUser').parent('tr')
          .find('td[data-label="Műveletek"] > .admin-actions > :nth-child(3) > .instructor-toggle')
          .click();
      cy.reload();
      cy.get('tbody > tr')
          .contains('td', 'TestUser')
          .parent('tr')
          .find('td[data-label="Oktató"]')
          .should('contain', 'Nem');
  });

  it('should delete the account', () => {
    cy.visit('http://localhost:8000/admin.php');
    const testAccount = cy.get('tbody > tr').contains('td', 'TestUser').parent('tr');
    testAccount.find('td[data-label="Műveletek"] > .admin-actions > [onsubmit="return confirm(\'Biztosan törölni szeretné a felhasználót?\');"] > .login-button').click();
    testAccount.should('not.exist');
    cy.get('tbody').should('not.contain', 'TestUser');
  });
});
