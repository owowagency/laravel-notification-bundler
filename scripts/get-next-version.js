#!/usr/bin/env node

import {execSync} from 'node:child_process';

const pattern = /Release note for version (?<version>.+):$/gm
const output = execSync('npx semantic-release --verify-conditions --dry-run', {
    encoding: 'utf-8',
    stdio: 'pipe',
});

const match = pattern.exec(output);

if (match && match.groups && 'version' in match.groups) {
    console.log(`next_version=${match.groups['version']}`);
    process.exit(0);
} else {
    console.error('Did not find next release in command output', output);
    process.exit(1);
}
