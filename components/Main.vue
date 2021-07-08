<template>
	<div>
		<!-- title -->
		<ap-title :data="title"></ap-title>

		<!-- text -->
		<ap-text class="mb-3" :data="text"></ap-text>

		<!-- input -->
		<ap-input ref="input" :data="input"></ap-input>

		<!-- buttons -->
		<div class="form-group mb-3">
			<ap-button :data="buttonCopy"></ap-button>
			<ap-button v-show="showShareBtn" :data="buttonShare"></ap-button>
		</div>

		<p v-show="people.length > 0" class="small mt-4">Usuarios que aceptaron tu invitación</p>

		<!-- list of people -->
		<ap-people :data="people"></ap-people>

		<!-- toast -->
		<ap-toast ref="toast"></ap-toast>
	</div>
</template>

<script>
	module.exports = {
		data: function () {
			// create the invitation link
			var invitationLink = 'http://apretaste.me/join/' + apretaste.request.username;

			// create list of ppl invited
			var people = [];
			apretaste.request.invited.forEach(function(item) {
				people.push({
					username: item.username,
					gender: item.gender,
					avatar: {picture:item.picture, letter:item.username, color:item.avatarColor},
					chips: [{icon:'fas fa-calendar', text:item.accepted, clear:true}],
					actions:[{icon:'fas fa-user', caption:'Visitar perfil', onTap:function(){apretaste.send({command:'PERFIL', data:{id:'@'+item.username}})}}]
				});
			});

			return {
				title: {
					text: 'Invita y gana'
				},
				text: {
					text: 'Invita a tu gente a la app, y ambos ganarán §3 cuando ellos usen Apretaste.'
				},
				input: {
					icon:'fas fa-link', 
					label:'Link de invitación',
					readOnly: true,
					value: invitationLink
				},
				buttonCopy: {
					icon: 'fas fa-copy',
					caption: 'Copiar link',
					size: 'medium',
					isPrimary: false,
					onTap: this.copy
				},
				buttonShare: {
					icon: 'fas fa-share-alt',
					caption: 'Compartir link',
					size: 'medium',
					isPrimary: true,
					onTap: this.share
				},
				people: people,
				showShareBtn: (apretaste.request.osType == 'android' || apretaste.request.osType == 'ios')
			}
		},
		methods: {
			copy() {
				this.$refs.input.copyToClipboard();
				this.$refs.toast.show("El link de invitación fue copiado");
			},
			share() {
				var link = $('#link').val();
				var text = "Instala Apretaste, la red de amistad de todos los cubanos y gánate §3 de bienvenida";
				apretaste.share({title: text, link: link});
			}
		}
	}
</script>

<style scoped>
</style>
