/**
 * ImagePicker.js a web component for selecting an image from a list
 */
class ImagePicker extends HTMLElement {
	static observedAttributes = ["value"];

	static template = `
	<style>
		:host > button {
			width: 100%;
			height: 100%;
		}
		:host > button img {
			width: 100%;
			height: 100%;
			display: block;
		}

		dialog {
			border: none
		}
		
		#picker button {
			background: none;
			border: none;
			padding: 0;
		}
	</style>
	<button id="selected"><img id="selected-image"></button>
	<dialog id="picker"></dialog>
	`;

	constructor() {
		super();

		const template = document.createElement("template");
		template.innerHTML = ImagePicker.template;

		this.attachShadow({mode:"open"});
		this.shadowRoot.appendChild(template.content.cloneNode(true));

		this.dialog = this.shadowRoot.getElementById("picker");
		this.dialog.addEventListener("close", () => {
			const value = this.dialog.returnValue;
			if (value) {
				this.setAttribute("value", value);
			}
		});

		this.selectedValueButton = this.shadowRoot.getElementById("selected");
		this.selectedValueButton.addEventListener("click", () => {
			this.dialog.showModal();
		});

		this.selectedValueImage = this.shadowRoot.getElementById("selected-image");
	}

	attributeChangedCallback(name, oldValue, newValue) {
		if (name === "value") {
			this.selectedValueImage.src = "/images/badges/" + newValue;
		}
	}

	get value() {
		return this.getAttribute("value");
	}

	set value(value) {
		return this.setAttribute("value", value);
	}

	set options(options) {
		if (!Array.isArray(options)) {
			// should we instead throw an Error?
			return;
		}

		// clear existing children
		for (const child of this.dialog.children) {
			dialog.removeChild(child);
		}

		const template = document.createElement("template");
		options.forEach((option) => {
			template.innerHTML = `<button commandfor="picker" command="close" value="${option}"><img src="/images/badges/${option}" /></button>`
			this.dialog.appendChild(template.content.cloneNode(true));
		});
	}
}

customElements.define("image-picker", ImagePicker);

/**
 * BadgeLevelInput.js a web component for creating a name/uses input pair in a
 * form
 */
class BadgeLevelInput extends HTMLElement {
	static observedAttributes = ["image", "name", "uses"];

	static template = `
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
	<style>
	:host {
		display: flex;
		gap: 1em;
	}
	button {
		background: none;
		border: none;
		padding: 0;
	}
	</style>
	<image-picker style="width: 2em" id="image"></image-picker>
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

		if (name === "image") {
			this.shadowRoot.getElementById("image").value = newValue;
		}
	}

	get value() {
		const image = this.shadowRoot.getElementById("image").value;
		const name = this.shadowRoot.getElementById("name").value;
		const uses = this.shadowRoot.getElementById("uses").value;

		return {
			image,
			name,
			uses
		};
	}

	set imageOptions(options) {
		this.shadowRoot.getElementById("image").options = options;
	}
}

customElements.define("badge-level-input", BadgeLevelInput);
