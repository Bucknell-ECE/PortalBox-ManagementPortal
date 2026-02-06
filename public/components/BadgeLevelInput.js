/**
 * BadgeLevelInput.js a web component for creating a name/uses input pair in a
 * form
 */
class BadgeLevelInput extends HTMLElement {
	static observedAttributes = ["name", "uses"];

	static template = `
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
	<style>
	:host {
		display: flex;
		gap: 1em;
	}
	:host button {
		background: none;
		border: none;
		padding: none;
	}
	</style>
	<label>Name: <input type="text" id="name" /></label>
	<label>Uses: <input type="number" min="1" size="5" id="uses" /></label>
	<button type="button" id="remove-button"><i class="material-icons">delete</i></button>
	`;

	constructor() {
		super();

		const template = document.createElement("template");
		template.innerHTML = BadgeLevelInput.template;

		this.attachShadow({mode:"open"});
		this.shadowRoot.appendChild(template.content.cloneNode(true));
		this.shadowRoot.getElementById("remove-button").addEventListener(
			"click",
			() => {
				this.remove();
			}
		);
	}

	attributeChangedCallback(name, oldValue, newValue) {
		if (name === "name") {
			this.shadowRoot.getElementById("name").value = newValue;
		}

		if (name === "uses") {
			this.shadowRoot.getElementById("uses").value = newValue;
		}
	}

	get value() {
		const name = this.shadowRoot.getElementById("name").value;
		const uses = this.shadowRoot.getElementById("uses").value;

		return {
			name,
			uses
		};
	}
}

customElements.define("badge-level-input", BadgeLevelInput);
