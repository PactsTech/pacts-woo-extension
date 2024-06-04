/**
 * @type {import('semantic-release').GlobalConfig}
 */
module.exports = {
  branches: [
    '+([0-9])?(.{+([0-9]),x}).x',
    'main',
    'next',
    'next-major',
    { name: 'beta', prerelease: true },
    { name: 'alpha', prerelease: true }
  ],
  plugins: [
    '@semantic-release/commit-analyzer',
    '@semantic-release/release-notes-generator',
    [
      '@semantic-release/github',
      {
        'assets': ['pacts-woo-extension.zip']
      }
    ],
    '@semantic-release/git'
  ]
};