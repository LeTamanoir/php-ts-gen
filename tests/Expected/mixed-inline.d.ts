declare namespace Typographos {
	export namespace Tests {
		export namespace Fixtures {
			export interface CompanyWithMixedAddresses {
				name: string
				headquartersAddress: {
					street: string
					city: string
					zipCode: string
				}
				mailingAddress: Typographos.Tests.Fixtures.Address
			}
			export interface Address {
				street: string
				city: string
				zipCode: string
			}
		}
	}
}
