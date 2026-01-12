<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escala de Trabalho</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
    
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    
                    <tr>
                        <td align="center" style="padding: 30px 20px 20px 20px; border-bottom: 1px solid #eeeeee;">
                            <img src="https://i.imgur.com/w02GqmZ.png" alt="Logo Band" style="display: block; border: 0;">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 30px 40px; color: #9c9c9cff; font-size: 16px; line-height: 1.5;">
                            <p style="margin-top: 0;">Olá pessoal,</p>
                            
                            <p>Segue a escala da proxima semana <strong style="font-size: 18px;">{{ $period }}</strong>.
                            </p>
                            
                            <p style="margin-bottom: 0;">Qualquer dúvida, estou à disposição.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #f9f9f9; padding: 15px 40px; border-top: 1px solid #eeeeee;">
                            <p style="font-size: 13px; color: #777777; margin: 0;">
                                <em>Enviado por: <strong>{{ $senderName }}</strong> via SGCM.</em>
                            </p>
                        </td>
                    </tr>
                    
                </table>
                
                <p style="font-size: 12px; color: #999999; margin-top: 20px;">
                    &copy; {{ date('Y') }} Sistema Gerenciador do Controle Mestre
                </p>

            </td>
        </tr>
    </table>

</body>
</html>