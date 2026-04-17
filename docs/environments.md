# Environments

Detailed comparison between the Production and Staging environments.

| Detail          | Production                | Staging                        |
| :-------------- | :------------------------ | :----------------------------- |
| **Domain**      | `pos.emanilaundry.com`    | `staging-pos.emanilaundry.com` |
| **Path**        | `/var/www/production/pos` | `/var/www/staging/pos`         |
| **Git Branch**  | `main`                    | `staging`                      |
| **Database**    | `pos_prod`                | `pos_staging`                  |
| **Queue Name**  | `pos_prod_queue`          | `pos_staging_queue`            |
| **PHP Version** | 8.4-fpm                   | 8.4-fpm                        |
| **SSL Status**  | Enabled (Certbot)         | Enabled (Certbot)              |

## Access Control

- **Production**: Access via main domain.
- **Staging**: Restricted access for testing new features before release.
