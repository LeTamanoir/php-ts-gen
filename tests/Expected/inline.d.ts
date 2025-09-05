declare namespace Typographos {
	export namespace Tests {
		export namespace Fixtures {
			export interface UserWithInlineAddress {
				name: string
				address: {
					street: string
					city: string
					zipCode: string
				}
				age: number
			}
		}
	}
}
