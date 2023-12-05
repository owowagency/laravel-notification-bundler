#!/usr/bin/env node

import {existsSync} from 'node:fs';

const files = ['composer.json'];

for (const file of files) {
    if (!existsSync(file)) {
        console.error('Could not find file', file);
        process.exit(1);
    }
}
