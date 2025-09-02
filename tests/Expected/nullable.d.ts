declare namespace Typographos {
    export namespace Tests {
        export namespace Fixtures {
            export interface Nullable {
                maybeString: string | null
                maybeInt: number | null
                maybeSelf: Typographos.Tests.Fixtures.Nullable | null
                maybeDate: unknown | null
            }
        }
    }
}
