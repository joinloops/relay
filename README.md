# Relay by Loops

Relay by Loops is an ActivityPub relay and discovery service designed primarily for ingesting public Loops content.

It helps Loops servers discover public videos and creators from across the network without requiring every server to independently discover and follow the same accounts.

## Purpose

Relay by Loops is built for:

- Discovering public Loops content
- Improving federation between Loops servers
- Reducing duplicated discovery work across participating servers
- Supporting broader ActivityPub ingestion over time

While the relay is optimized for Loops, it may also consume compatible public content from other ActivityPub platforms.

## How It Works

Participating servers subscribe to the relay through ActivityPub.

The relay receives public activities from connected servers and distributes eligible activities to other subscribers. Loops servers can then process those activities for discovery, recommendations, moderation, and federation.

Relay by Loops is intended specifically for content ingestion and discovery. It is not a general-purpose ActivityPub relay.

## Scope

Relay by Loops currently focuses on:

- Public video content
- Loops-compatible ActivityPub objects
- Server-to-server federation

Support for additional ActivityPub software and object types may be added as compatibility improves.

## Privacy

Relay by Loops only processes content that is publicly federated through ActivityPub.

It does not provide access to:

- Private posts
- Followers-only posts
- Direct messages
- Non-federated content

Server administrators remain responsible for how relayed content is stored, displayed, filtered, and moderated on their own servers.

## Moderation

Relay by Loops may reject, filter, suspend, or remove servers and activities that create moderation, operational, security, or abuse concerns.

Subscribing servers should continue to maintain their own:

- Domain blocklists
- Content moderation policies
- Safety filters
- Abuse reporting systems

Participation in the relay does not replace local moderation.

## Development Status

Relay by Loops is under active development.

Federation behaviour, supported activity types, compatibility, and APIs may change while the service matures.

## Loops

Relay by Loops is part of the [Loops ecosystem](https://joinloops.org).

## License

This project is open-sourced software licensed under the [AGPLv3 license](LICENSE).

## Funding

This project is funded through [NGI Zero Core](https://nlnet.nl/core),  
a fund established by [NLnet](https://nlnet.nl) with support from the European Commission’s [Next Generation Internet](https://ngi.eu) program.  
Learn more at the [NLnet project page](https://nlnet.nl/project/Loops).

[<img src="https://nlnet.nl/logo/banner.png" alt="NLnet foundation logo" width="20%" />](https://nlnet.nl)
[<img src="https://nlnet.nl/image/logos/NGI0_tag.svg" alt="NGI Zero Logo" width="20%" />](https://nlnet.nl/core)

## Supporters

Thanks to the Fastly Fast Forward program, Loops uses Fastly CDN and Object Storage to serve videos globally for free.

[<img src="https://github.com/user-attachments/assets/f1499b1f-c05f-480a-a5d5-dbebcb0e20fd" alt="Fastly Fast Forward logo" width="50%" />](https://www.fastly.com/fast-forward)

## Stargazing

[![Star History Chart](https://api.star-history.com/svg?repos=joinloops/relay&type=Date)](https://star-history.com/#joinloops/relay&Date)
