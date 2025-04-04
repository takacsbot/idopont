const { defineConfig } = require('cypress');

module.exports = defineConfig({
	e2e: {
		setupNodeEvents(on, config) {
			// implement node event listeners here
		},
		retries: {
			runMode: 2,
			openMode: 0
		},
		defaultCommandTimeout: 8000,
		pageLoadTimeout: 60000,
    cacheAcrossSpecs: true,
    experimentalSessionAndOrigin: true,
	}
});