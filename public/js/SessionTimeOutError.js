/**
 * SessionTimeOutError can be thrown when the API session times out. I.e. in
 * response to HTTP 403 Status
 */
class SessionTimeOutError extends Error {
	constructor(message) {
		super(message);
		this.name = 'SessionTimeOutError';
	}
}

export { SessionTimeOutError };
