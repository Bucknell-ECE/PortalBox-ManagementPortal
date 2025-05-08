# Maker Portal
Credit to Tom Egan (tom@tomegan.tech) for the bulk of this repository. Updates from his version are the addition of PINs, and resources for cloud deployment through AWS CloudFormation. If you are looking for the original MakerPortal, visit https://github.com/Bucknell-ECE/PortalBox-ManagementPortal.

## CloudFormation Instructions
1. Download `makerportal-template.yaml`.
2. Login to the AWS Console.
3. If you do not have a private keypair, create one in EC2.
4. Go to AWS CloudFormation, click "Create Stack", upload from template with the downloaded file.
5. Fill out your Name, Email, Password (for the database), Google OAuth Client-ID, and select your keypair.
6. Click through to build the stack.
7. Voila! Within 15-20 minutes your MakerPortal stack will be ready. You can register a domain of your choice on AWS Route 53 or another registrar.

## Further Configuration After CloudFormation
Use Remote SSH to login to your EC2 instance with your keypair from the AWS Console. 
- You can then navigate through the server. You may need to run `sudo chown -R ec2-user:ec2-user .` to make files temporarily saveable, then `sudo chown -R apache:apache .` to return ownership to the server.
- Run `sudo systemctl restart httpd` and `sudo systemctl restart php-fpm` to restart the server properly after making changes.
- To update styling of the MakerPortal, visit `/var/www/html/portalbox/public/styles/styles.css`.
- To update `index.html`, which styles the main menu, visit `/var/www/html/portalbox/public/index.html`.
- If you need to change any configuration values, edit `/var/www/html/portalbox/config/config.ini`.
