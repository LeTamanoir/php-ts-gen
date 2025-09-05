declare namespace Typographos {
	export namespace Tests {
		export namespace Fixtures {
			export interface InlineRecords {
				inlineScalars: {
					string: string
					int: number
					float: number
					bool: boolean
					object: object
					mixed: any
					null: null
					true: true
					false: false
					noType: unknown
				}
				scalars: Typographos.Tests.Fixtures.Scalars
			}
			export interface Scalars {
				string: string
				int: number
				float: number
				bool: boolean
				object: object
				mixed: any
				null: null
				true: true
				false: false
				noType: unknown
			}
		}
	}
}
